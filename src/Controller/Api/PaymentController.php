<?php

namespace App\Controller\Api;

use App\Entity\Donor;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Route(
    path: '/api',
    name: 'api_',
)]
class PaymentController extends ApiController
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/create-checkout-session', name: 'create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(Request $request): JsonResponse
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $content = json_decode($request->getContent(), true);
        $amount = $content['amount'];
        $name = $content['name'];
        $message = $content['message'];
        $locale = $content['locale'];
        
        if (strlen($name) > 70 || strlen($message) > 70) {
            return new JsonResponse(['error' => 'Error 70 caractères.'], 400);
        }
        
        try {
            $session = Session::create([
                'payment_method_types' => ['card'],
                'line_items' => [[
                    'price_data' => [
                        'currency' => 'eur',
                        'product_data' => [
                            'name' => 'Donation',
                        ],
                        'unit_amount' => $amount * 100, 
                    ],
                    'quantity' => 1,
                ]],
                'mode' => 'payment',
                'success_url' => $_ENV['DOMAIN_FRONT'] . $locale . '/dons?status=success',
                'cancel_url' => $_ENV['DOMAIN_FRONT'] .  $locale . '/dons?status=error',
            ]);

            $donor = new Donor();
            $donor->setName(!empty($name) ? $name : 'Anonymous');
            $donor->setMessage($message);
            $donor->setAmount($amount);
            $donor->setLocale($locale);

            $this->entityManager->persist($donor);
            $this->entityManager->flush();

            return new JsonResponse(['id' => $session->id]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/donors', name: 'get_donors', methods: ['GET'])]
    public function getDonors(): JsonResponse
    {
        $donors = $this->entityManager->getRepository(Donor::class)->findBy([], ['id' => 'DESC']);
        
        $donorData = array_map(function($donor) {
            return [
                'id' => $donor->getId(),
                'name' => $donor->getName(),
                'message' => $donor->getMessage(),
                'amount' => $donor->getAmount(),
                'locale' => $donor->getLocale(),
            ];
        }, $donors);

        return new JsonResponse($donorData);
    }
}
