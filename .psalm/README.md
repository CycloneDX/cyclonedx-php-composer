# PSALM things

## baselines

generate via 
```
tools/psalm/vendor/bin/psalm --set-baseline=.psalm/baseline-<TYPE>.xml
```

* [`baseline-lowest`](baseline-lowest.xml) was built using the lowest supported env:
  - the lowest requirements (`composer update --prefer-lowest`)
  - with [composer1](https://getcomposer.org/composer-1.phar)
  - on a php7.1
* [`baseline-stable`](baseline-stable.xml) was built using a current stable env:
  - stable requirements (`composer update --prefer-stable`)
  - with [composer2](https://getcomposer.org/composer-2.phar)
  - on a non-outdated php>=7.3
