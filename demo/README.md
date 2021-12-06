# Demo

There are multiple demo projects:

* [`symfony`](symfony/README.md)
  which composer-requires `symfony/symfony:@stable` in a fluid unlocked version.  
  The demo is intended to run on every environment.  
  This also means, the output might not be reproducible on independent systems, 
  due to the fact that version-locks are fluid, and the composer-lock file is not shipped as code.
* [`laravel-7.12.0`](laravel-7.12.0/README.md)
  which composer-requires `laravel/framework:7.12.0`.  
  The output is _reproducible_, due to the shipped composer-locked versions.
  Therefore, the demo requires a special php environment.  
  *ATTENTION*: this demo might use vulnerable dependencies for showcasing purposes.
* [`local`](local/README.md)
  which composer-requires a private/local package `cyclonedx/cyclonedx-php-composer-local-demo-dependency` and other locals.  
  The output is _reproducible_, due to the shipped composer-locked versions.
  Therefore, the demo requires a special php environment.  

Purpose is to demonstrate how _cyclonedx-php-composer_ integrates, can be used,
and how the generated output will look like.

## Maintenance 

Files in `**/project/**` are marked as `linguist-vendored` in the `.gitattributes`.  
Therefore, the requirements/dependencies are 
[not maintained by dependabot](https://docs.github.com/en/code-security/supply-chain-security/configuration-options-for-dependency-updates#vendor).

Files in `**/example-results/**` are marked as `linguist-generated` in the `.gitattributes`.  
Files in `**/results/**` are marked as `linguist-generated` in the `.gitattributes`.  
