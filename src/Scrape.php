<?php

namespace App;

require '../vendor/autoload.php';

class Scrape
{
    private array $products = [];

    public function run(): void
    {   
        $baseUrl = 'https://www.magpiehq.com/developer-challenge/smartphones';
        $currentPage = 1;
        do {
            $url = $baseUrl . "/?page=" . $currentPage;
            $document = ScrapeHelper::fetchDocument($url);

            $productElements = $document->filter('.product');
            if ($productElements->count() > 0) {
                $productElements->each(function ($productElement) use ($baseUrl) {

                    $colorElements = $productElement->filter('.border.border-black.rounded-full.block');
                    $colorElements->each(function ($colorElement) use ($productElement, $baseUrl) {
                        
                        $product = new Product($productElement, $colorElement, $baseUrl);
                        
                        $uniqueIdentifier = $product->getTitle() . '_' . $product->getColor();
        
                        if (!$this->isDuplicate($uniqueIdentifier)) {
                            $this->products[] = [
                                'title' => $product->getTitle(),
                                'price' => $product->getPrice(),
                                'imgUrl' => $product->getImgUrl(),
                                'capacityMB' => $product->getCapacity(),
                                'color' => $product->getColor(),
                                'availabilityText' => $product->getAvailabilityText(),
                                'isAvailable' => $product->getIsAvailable(),
                                'shippingText' => $product->getShippingText(),
                                'shippingDate' => $product->getShippingDate()
                            ];
                        }
                    });
                });

                $currentPage++;
                
            } else {
                break;
            }
        } while (true);


        file_put_contents('output.json', json_encode($this->products, JSON_UNESCAPED_SLASHES));
    }

    private function isDuplicate(string $uniqueIdentifier): bool
    {
        foreach ($this->products as $existingProduct) {
            $existingIdentifier = $existingProduct['title'] . '_' . $existingProduct['color'];
            if ($uniqueIdentifier === $existingIdentifier) {
                return true;
            }
        }
        return false;
    }
}

$scrape = new Scrape();
$scrape->run();
