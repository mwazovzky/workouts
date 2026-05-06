<?php

namespace App\Http\Controllers;

use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Prometheus\Storage\Adapter;

class MetricsController extends Controller
{
    public function __construct(private readonly Adapter $storage) {}

    public function __invoke(): \Illuminate\Http\Response
    {
        $registry = new CollectorRegistry($this->storage, false);
        $output = (new RenderTextFormat)->render($registry->getMetricFamilySamples());

        return response($output, 200, ['Content-Type' => RenderTextFormat::MIME_TYPE]);
    }
}
