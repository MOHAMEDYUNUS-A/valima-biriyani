<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Env;
use App\Helpers\Request;
use App\Helpers\Session;

class SettingsController extends BaseController
{
    /**
     * Settings & Integrations overview page.
     */
    public function index(Request $request): void
    {
        $this->requireRoles(['owner', 'manager']);

        // Razorpay Integration Status
        $rzpKey = Env::get('RAZORPAY_KEY_ID', '');
        $rzpSecret = Env::get('RAZORPAY_KEY_SECRET', '');
        if (empty($rzpKey) || str_contains($rzpKey, 'placeholder')) {
            $razorpayStatus = ['status' => 'not_configured', 'label' => 'Not Configured', 'badge' => 'neutral'];
        } elseif (str_starts_with($rzpKey, 'rzp_test_')) {
            $razorpayStatus = ['status' => 'test_mode', 'label' => 'Test Mode', 'badge' => 'warning'];
        } elseif (str_starts_with($rzpKey, 'rzp_live_')) {
            $razorpayStatus = ['status' => 'live', 'label' => 'Live', 'badge' => 'success'];
        } else {
            $razorpayStatus = ['status' => 'test_mode', 'label' => 'Configured', 'badge' => 'info'];
        }

        // WhatsApp Integration Status
        $waCloudApi = Env::get('WHATSAPP_USE_CLOUD_API', 'false') === 'true';
        $waToken = Env::get('WHATSAPP_TOKEN', '');
        if ($waCloudApi && !empty($waToken)) {
            $whatsappStatus = ['status' => 'live', 'label' => 'Cloud API (Live)', 'badge' => 'success'];
        } else {
            $whatsappStatus = ['status' => 'free_active', 'label' => 'Free wa.me Links (Active)', 'badge' => 'success'];
        }

        // Make.com Webhook Status
        $makeWebhook = Env::get('MAKE_WEBHOOK_URL', '');
        if (empty($makeWebhook) || str_contains($makeWebhook, 'example.com')) {
            $makeStatus = ['status' => 'not_configured', 'label' => 'Not Configured', 'badge' => 'neutral'];
        } else {
            $makeStatus = ['status' => 'active', 'label' => 'Active', 'badge' => 'success'];
        }

        // Mail / SMTP Status
        $mailHost = Env::get('MAIL_HOST', 'localhost');
        if (empty($mailHost) || $mailHost === 'localhost') {
            $mailStatus = ['status' => 'not_configured', 'label' => 'Local / Disabled', 'badge' => 'neutral'];
        } else {
            $mailStatus = ['status' => 'live', 'label' => 'Configured (' . $mailHost . ')', 'badge' => 'success'];
        }

        $this->render('admin.settings.index', [
            'title' => 'Integrations & Store Settings',
            'currentPage' => 'settings',
            'currentUser' => Session::user(),
            'integrations' => [
                'razorpay' => $razorpayStatus,
                'whatsapp' => $whatsappStatus,
                'make' => $makeStatus,
                'mail' => $mailStatus,
            ],
            'config' => [
                'store_name' => 'Valima Biriyani',
                'store_tagline' => 'Biriyani Specialist',
                'currency' => 'INR (₹)',
                'tax_rate' => '5% GST',
                'delivery_fee' => '₹30.00 (Free over ₹500)',
                'phone' => '+91 98765 43210',
                'address' => 'No. 12, 2nd Street, Anna Nagar, Chennai - 600040',
            ]
        ], 'admin');
    }
}
