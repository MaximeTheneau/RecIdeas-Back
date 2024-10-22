<?php

namespace App\Controller\Api;

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
    #[Route('/create-checkout-session', name: 'create_checkout_session', methods: ['POST'])]
    public function createCheckoutSession(Request $request): JsonResponse
    {
        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        $content = json_decode($request->getContent(), true);
        $amount = $content['amount'];
        $name = $content['name'];
        $messge = $content['message'];


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
                'success_url' => $_ENV['DOMAIN_FRONT'] . '/donate?status=success',
                'cancel_url' => $_ENV['DOMAIN_FRONT'] . '/donate?status=error',
            ]);

            return new JsonResponse(['id' => $session->id]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
