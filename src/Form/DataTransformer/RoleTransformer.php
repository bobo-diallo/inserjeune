<?php

namespace App\Form\DataTransformer;

use Symfony\Component\Form\DataTransformerInterface;

class RoleTransformer implements DataTransformerInterface
{

   public function transform($value)
   {
      dump($value);
      return implode(',', $value);
   }

   public function reverseTransform($value)
   {
   }
}
