<?php 
namespace App\Service;

use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Imagine\Image\Point;

use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\DependencyInjection\ParameterBag\ContainerBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\SerializerInterface;
use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Symfony\Component\HttpClient\HttpClient;
use Aws\S3\S3Client;
use Aws\Exception\AwsException;

class ImageOptimizer
{
    private $slugger;
    private $params;
    private $serializer;
    private $photoDir;
    private $projectDir;
    private $imagine;
    private $uploadApi;
    private const IMAGE_SIZES = [320, 640, 750, 828, 1080, 1200, 1920, 2048, 3840];

    public function __construct(
        SluggerInterface $slugger,
        ContainerBagInterface $params,
        SerializerInterface $serializer,
        )
        {      
            $this->slugger = $slugger;
            $this->params = $params;
            $this->serializer = $serializer;
            $this->photoDir =  $this->params->get('app.imgDir');
            $this->projectDir =  $this->params->get('app.projectDir');
            $this->s3Key = $this->params->get('amazon.s3.key');
            $this->s3Secret = $this->params->get('amazon.s3.secret');
            $this->s3Region = $this->params->get('amazon.s3.region');
            $this->s3Bucket = $this->params->get('amazon.s3.bucket');
            $this->s3BucketFront = $this->params->get('amazon.s3.bucket.front');
            $this->s3Version = $this->params->get('amazon.s3.version');
            $this->domainImg = $this->params->get('app.domain.img');
            $this->s3Client = new S3Client([
                'version' => $this->s3Version,
                'region' => $this->s3Region,
                'credentials' => [
                    'key' => $this->s3Key,
                    'secret' => $this->s3Secret,
                ],
            ]);
            $this->imagine = new Imagine();

            // $this->uploadApi = Configuration::instance();
            // $this->uploadApi->cloud->cloudName = $_ENV['CLOUD_NAME'];
            // $this->uploadApi->cloud->apiKey = $_ENV['CLOUD_API_KEY'];
            // $this->uploadApi->cloud->apiSecret = $_ENV['CLOUD_API_SECRET'];
            // $this->uploadApi->url->secure = true;
            // $this->uploadApi = new UploadApi();
    }

    public function setPicture( $brochureFile, $post, $slug ): void
    {   
        $localImagePath = $this->photoDir . $slug . '.webp'; // Path Local Image

        $imageS3Path = $this->s3Bucket . '/' . $slug . '.webp'; // Path S3 Image

        $img = $this->imagine->open($brochureFile);

        $img->strip()->save($localImagePath, ['webp_quality' => 80]);

        if ($post->getImgPost() !== null) {
            
            $bucketDomain = $_ENV['DOMAIN_IMG']; 
            $key = str_replace($bucketDomain, "", $post->getImgPost());

            $this->s3Client->deleteObject([
                'Bucket' => $this->s3Bucket,
                'Key'    => $key,
            ]);

            $this->s3Client->deleteMatchingObjects($this->s3BucketFront, $slug . '.webp');

            $this->s3Client->deleteObject([
                'Bucket' => $this->s3BucketFront,
                'Key'    => $post->getImgPost(),
            ]);

            $slug = $slug . '-' . rand(0, 10);

            $this->s3Client->putObject([
                'Bucket' => $this->s3Bucket,
                'Key'    => $slug . '.webp',
                'Body'   => fopen($localImagePath, 'rb'),
            ]);
        }
        
        // Srcset Image
        $srcset = '';
        
        $imgUrl = $this->domainImg . $slug . '.webp';
        foreach (self::IMAGE_SIZES as $size) {
            if($size <= $img->getSize()->getWidth()) {
                $srcset .= $imgUrl . '?width=' . $size . ' ' . $size . 'w,';
            }
        }
        
        $srcset .= $imgUrl . ' ' . $img->getSize()->getWidth() . 'w';
        
        try {
            $this->s3Client->putObject([
                'Bucket' => $this->s3Bucket,
                'Key'    => $slug . '.webp',
                'Body'   => fopen($localImagePath, 'rb'),
            ]);
            
            $post->setImgPost($imgUrl); // Url Image
            $post->setSrcset($srcset); // Srcset Image
            $post->setImgWidth($img->getSize()->getWidth()); // Width Image
            $post->setImgHeight($img->getSize()->getHeight()); // Height Image

        } catch (AwsException $e) {
            echo $e->getMessage();
        } finally {
            unlink($localImagePath);
        }

    }

    public function deletedPicture($slug): void
    {
        try {
            $this->s3Client->deleteObject([
                'Bucket' => $this->s3Bucket,
                'Key'    => $slug . '.webp',
            ]);

            $this->s3Client->deleteMatchingObjects($this->s3BucketFront, $slug . '.webp');

            $this->s3Client->deleteObject([
                'Bucket' => $this->s3BucketFront,
                'Key'    => $slug . '.webp',
            ]);
        } catch (AwsException $e) {
            echo $e->getMessage();
        }
      
    }

}



