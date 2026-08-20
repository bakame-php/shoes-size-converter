# Bakame Shoes Size Converter

This small library was prompted by the following post
https://phpc.social/@nando161@partyon.xyz/117123893682362282

Converting shoes size is difficult, too difficult!!

![Too many standards](https://imgs.xkcd.com/comics/standards.png)

So I found [schuhgroessen-umrechner](https://github.com/sarah-schuh/schuhgroessen-umrechner))
and based on its amazing work I wrote this little library.

All functionalities where already present in the base repository I just
restructured it to make it available as a standalone library.

So all credits should go to [Sarah Amft](https://github.com/sarah-schuh)

## System Requirements

- **PHP >= 8.2** is required but the latest stable version of PHP is recommended.

## Installation

**Shoes size converter** is available on [Packagist](https://packagist.org/packages/bakame/shoes-size-converter) and can be installed using [Composer](https://getcomposer.org/):

~~~
composer require bakame/shoes-size-converter
~~~

## Usage

```php
use Bakame\Shoes\ShoeSize;
use Bakame\Shoes\Unit;
use Bakame\Shoes\Converter;

$path = __DIR__ . '/data/shoe_sizes.csv';
$converter = Converter::fromPath($path);
$ueShoeSize = Unit::Eu->size(39);
$ueShoeSize->value;
// returs 39
$ueShoeSize->unit;
// returs Unit::Eu

$ukShoeSize = $converter->convert($ueShoeSize, Unit::Uk);
$ukShoeSize->value; 
// returns 6; 

$ukShoeSize->unit
// returs Unit::Uk

$converter->availableSizes(Unit::Eu); 
// returns all available shoes sizes in EU 
// as an iterator of ShoesSize instance

$converter->inCm(Unit::Uk->size(12));
// returns 29.6
// the shoe size in centimeters

$converter->inInch(Unit::Uk->size(12));
// returns 11.653543307087
// the shoe size in inches
// it is up to the user to format
// the output with number_format for instance

$result = $converter->equivalents($ueShoeSize);
// to list all shoesizes in all supported format
```

## Contributing

Contributions are welcome and will be fully credited. Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CONDUCT](.github/CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email nyamsprod@gmail.com instead of using the issue tracker.

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Credits

- [ignace nyamagana butera](https://github.com/nyamsprod)
- [Sarah Amft](https://github.com/sarah-schuh)
- [All Contributors](https://github.com/bakame-php/shoes-size-converter/graphs/contributors)

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.

**Happy Coding!**
