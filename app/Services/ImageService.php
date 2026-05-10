<?php

namespace App\Services;

use Cloudinary\Cloudinary;

class ImageService
{
   private Cloudinary $cloudinary;

   public function __construct()
   {
      $this->cloudinary = new Cloudinary(env('CLOUDINARY_URL'));
   }

   /**
    * Загрузить одно изображение
    */
   public function upload($file, string $folder = 'cars'): ?string
   {
      $uploadResult = $this->cloudinary->uploadApi()->upload(
         $file->getRealPath(),
         ['folder' => $folder, 'quality' => 'auto', 'fetch_format' => 'auto']
      );

      return $this->extractSecureUrl($uploadResult);
   }

   /**
    * Загрузить несколько изображений
    */
   public function uploadMultiple(array $files, string $folder = 'cars'): array
   {
      $urls = [];
      foreach ($files as $file) {
         $url = $this->upload($file, $folder);
         if ($url)
            $urls[] = $url;
      }
      return $urls;
   }

   /**
    * Извлечь secure_url из результата Cloudinary
    */
   private function extractSecureUrl($result): ?string
   {
      if ($result instanceof \ArrayObject) {
         $data = $result->getArrayCopy();
         return $data['secure_url'] ?? null;
      }
      if (is_array($result)) {
         return $result['secure_url'] ?? null;
      }
      if (is_object($result)) {
         $data = json_decode(json_encode($result), true);
         return $data['secure_url'] ?? null;
      }
      return null;
   }
}