# Demo

There are multiple demo projects:

* [`symfony`](symfony/README.md)
  which composer-requires `symfony/symfony:@stable` in a fluid unlocked version.  
  The demo is intended to run  on every environment.  
  This also means, the output might not be reproducible on independent systems, 
  due to the fact that version-locks are fluid, and the composer-lock file is not sipped as code.
* [`laravel-7.12.0`](laravel-7.12.0/README.md) 
  which composer-requires `laravel/framework:7.12.0`"`.  
  The output is reproducible, due to the shipped composer-locked versions.
  Therefore, the demo requires a special php environment.

Purpose is to demonstrate how _cyclonedx-php-composer_ integrates, can be used,
and how the generated output will look like.
