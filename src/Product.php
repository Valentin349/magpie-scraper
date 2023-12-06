<?php

namespace App;

class Product
{
    private string $baseUrl;
    private string $title;
    private string $capacityText;
    private string $price;
    private string $imgUrl;
    private string $color;
    private string $availabilityText;
    private string $shippingText = "";

    function __construct($productElement, $colorElement, $url)
    {
        $this->baseUrl = $url;

        $this->title = $productElement->filter('.product-name')->text();
        $this->capacityText = $productElement->filter('.product-capacity')->text();
        $this->price = $productElement->filter('.my-8.block.text-center.text-lg')->text();
        $this->imgUrl = $productElement->filter('img')->attr('src');
        $this->color = $colorElement->attr('data-colour');

        $availabilityElement = $productElement->filter('.my-4.text-sm.block.text-center');
        $this->availabilityText = $availabilityElement->text();

        // check if node list empty
        if (count($availabilityElement) > 1){
            $this->shippingText = $availabilityElement->nextAll('.my-4.text-sm.block.text-center')->text();
        }

    }

    public function getTitle(): string
    {
        return $this->title . " " . $this->capacityText;
    }

    public function getCapacity(): float
    {

        $multiplier = 1;

        if (stripos($this->capacityText, 'GB') !== false) {
            $multiplier = 1024;
        }

        preg_match('/[\d.]+/', $this->capacityText, $matches);
        $capacity = (float)$matches[0] * $multiplier;

        return $capacity;
    }

    public function getImgUrl(): string
    {
        return $this->baseUrl . '/' . ltrim($this->imgUrl, './');
    }

    public function getPrice(): float
    {
        return floatval(preg_replace('/[^0-9.]/', '', $this->price));
    }

    public function getColor(): string
    {
        return $this->color;
    }

    public function getAvailabilityText(): string
    {
        return $this->availabilityText;
    }

    public function getIsAvailable(): bool
    {
        return stripos($this->availabilityText, 'In Stock') !== false;
    }

    public function getShippingText(): string
    {
        return $this->shippingText;
    }

    public function getShippingDate(): string
    {
        $matches = [];
        $patterns = [
            '/(\d{4}-\d{2}-\d{2})/',   
            '/(\d{2} \w+ \d{4})/',            
            '/(\d{1,2} \w+ \d{4})/',          
            '/(\d{4}-\d{2}-\d{2})/',          
            '/\b(\d{1,2} \w+ \d{4})\b/',
            '/(\w+ \d{1,2}(?:st|nd|rd|th) \w+ \d{4})/',
            '/(?:\b\w+\b\s+)?(?:today|tomorrow)/i'    
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $this->shippingText, $matches)) {
                // check if preg_match adds a value to $matches
                $date = isset($matches[1]) ? $matches[1] : $matches[0];

                if (strtolower(trim($date)) === 'today') {
                    $date = date('Y-m-d');
                } elseif (strtolower(trim($date)) === 'tomorrow') {
                    $date = date('Y-m-d', strtotime('+1 day'));
                } else {
                    $date = date('Y-m-d', strtotime($date));
                }

                return $date;
            }
        }

        return "";
    }
    
}
