<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Helpers\Request;
use App\Helpers\View;
use App\Models\MenuCategory;
use App\Models\MenuItem;

class PublicController extends BaseController
{
    public function home(Request $request): void
    {
        $categories = MenuCategory::allActive();
        $items = MenuItem::allActive();

        $this->render('customer.home', [
            'title' => 'Authentic Dum Biriyani & Royal Delicacies',
            'categories' => $categories,
            'featuredItems' => array_slice($items, 0, 6),
        ], 'main');
    }

    public function about(Request $request): void
    {
        $this->render('customer.about', [
            'title' => 'Our Story & Firewood Dum Heritage',
        ], 'main');
    }

    public function contact(Request $request): void
    {
        $this->render('customer.contact', [
            'title' => 'Contact Us & Locations',
        ], 'main');
    }

    public function hoursAndLocation(Request $request): void
    {
        $this->render('customer.hours_location', [
            'title' => 'Operating Hours & Dining Location',
        ], 'main');
    }

    public function faq(Request $request): void
    {
        $this->render('customer.faq', [
            'title' => 'Frequently Asked Questions',
        ], 'main');
    }

    public function privacyPolicy(Request $request): void
    {
        $this->render('customer.policies.privacy', [
            'title' => 'Privacy Policy',
        ], 'main');
    }

    public function termsOfService(Request $request): void
    {
        $this->render('customer.policies.terms', [
            'title' => 'Terms of Service',
        ], 'main');
    }

    public function refundPolicy(Request $request): void
    {
        $this->render('customer.policies.refund', [
            'title' => 'Refund & Cancellation Policy',
        ], 'main');
    }

    public function deliveryPolicy(Request $request): void
    {
        $this->render('customer.policies.delivery', [
            'title' => 'Delivery Policy & Service Area',
        ], 'main');
    }
}
