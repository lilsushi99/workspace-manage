<?php

namespace App\Http\Controllers;

use App\Models\ContentSection;
use Illuminate\Http\Request;

class MarketingController extends Controller
{
    public function index()
    {
        $sections = ContentSection::with(['items' => function ($q) {
            $q->where('status', 'active')->orderBy('sort_order');
        }])->where('status', 'active')->get()->keyBy('key');

        return view('welcome', compact('sections'));
    }

    public function features()
    {
        $featuresSection = ContentSection::with(['items' => function ($q) {
            $q->where('status', 'active')->orderBy('sort_order');
        }])->where('key', 'features')->first();

        return view('marketing.features', compact('featuresSection'));
    }

    public function pricing()
    {
        return view('marketing.pricing');
    }

    public function resources()
    {
        return view('marketing.resources');
    }

    public function contact()
    {
        return view('marketing.contact');
    }
}
