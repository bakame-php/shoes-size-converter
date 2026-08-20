# Bakame Shoes Size Converter

This small library was prompted by the following post
https://phpc.social/@nando161@partyon.xyz/117123893682362282

Converting shoes size is difficult, too difficult!!

![Too many standards](https://imgs.xkcd.com/comics/standards.png)

So I found [schuhgroessen-umrechner](https://github.com/sarah-schuh/schuhgroessen-umrechner) and, inspired by Sarah's excellent work, built this small library around it.

The original repository already contained all the core functionality. I mainly restructured and packaged it as a standalone library with a reusable API.

All credit for the original work goes to [Sarah Amft](https://github.com/sarah-schuh).

## System Requirements

- **PHP >= 8.2** is required but the latest stable version of PHP is recommended.

## Installation

**Shoes size converter** is available on [Packagist](https://packagist.org/packages/bakame/shoes-size-converter) and can be installed using [Composer](https://getcomposer.org/):

~~~
composer require bakame/shoe-size-converter
~~~

## Usage

```php
use Bakame\Shoes\ShoeSize;
use Bakame\Shoes\Unit;
use Bakame\Shoes\Converter;

$path = __DIR__ . '/data/shoe_sizes.csv';
$converter = Converter::fromPath($path);
$euShoeSize = Unit::Eu->size(39);
$euShoeSize->value;
// returs 39
$euShoeSize->unit;
// returs Unit::Eu

$ukShoeSize = $converter->inUnit($euShoeSize, Unit::Uk);
$ukShoeSize->value; 
// returns 6; 

$ukShoeSize->unit
// returs Unit::Uk

$converter->availableSizes(Unit::Eu); 
// returns all available shoes sizes in EU 
// as an iterator of ShoeSize instance

$converter->inCm(Unit::Uk->size(12));
// returns 29.6
// the shoe size in centimeters

$converter->inInch(Unit::Uk->size(12));
// returns 11.653543307087
// the shoe size in inches
// it is up to the user to format
// the output with number_format for instance

$result = $converter->equivalents($euShoeSize);
// to list all shoesizes in all supported format
```

## Examples

Sara's original script is preserved in the `examples` directory and has been
adapted to work as an API endpoint that returns JSON responses only. See
[index.php](examples/index.php).

## Database

The package continues to rely on a CSV file, which can be updated, replaced,
or cached by the application as needed. Managing the CSV file and providing
database or other storage backends are outside the scope of this package.

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
