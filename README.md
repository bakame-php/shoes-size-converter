# Bakame Shoes Size Converter

This small library was prompted by the following post
https://phpc.social/@nando161@partyon.xyz/117123893682362282

Converting shoes size is difficult, too difficult!!

![Too many standards](https://imgs.xkcd.com/comics/standards.png)

I discovered [schuhgroessen-umrechner](https://github.com/sarah-schuh/schuhgroessen-umrechner)
and, inspired by Sarah's excellent work, built this small library as an
independent reimplementation.

## System Requirements

- **PHP >= 8.2** is required but the latest stable version of PHP is recommended.

## Installation

**Shoe size converter** is available on [Packagist](https://packagist.org/packages/bakame/shoes-size-converter) and can be installed using [Composer](https://getcomposer.org/):

~~~
composer require bakame/shoe-size-converter
~~~

## Usage

```php
use Bakame\Shoes\ShoeSize;
use Bakame\Shoes\Unit;
use Bakame\Shoes\Converter;

$path = __DIR__ . '/data/shoe_sizes.csv';
$converter = Converter::fromCsv($path);
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

The original script by Sarah Amft has been reimplemented using this library's API
in the `examples` directory. It exposes the conversion functionality as an HTTP
API endpoint that returns JSON responses. See [converter.php](examples/converter.php).

To run the converter, go to the root directory and start PHP's built-in
development server:

```bash
php -S localhost:4000
````

Then open your browser or HTTP client and request:

```text
http://localhost:4000/examples/converter.php?unit=EU&size=42
```

This returns:

```json
{"sizes":[{"value":27.1,"unit":"CM"},{"value":42,"unit":"EU"},{"value":9.5,"unit":"US"},{"value":9,"unit":"UK"}],"measurements":{"centimeters":27.1,"inches":10.669291338582678}}
```

The same script can also be used as a CLI command:

```bash
php examples/converter.php EU 42
```

This returns:

```bash
Shoe size conversion
Input
  EU 42
Sizes
  CM 27.1
  EU 42
  US 9.5
  UK 9
Measurements
  27.1 cm
  10.669291338583 in
```

## Persistence Layer

The package relies on the `League\Csv\TabularData` interface for reading
shoe-size data. You can load data from either a CSV file or a database table.

Both sources must follow the expected shoe-size structure documented by the
corresponding factory methods:

- `fromCsv()` — loads data from a CSV file whose first row contains the
  `EU`, `US`, `UK`, and `CM` column headers.
- `fromPdo()` — loads data from a `shoe_sizes` database table containing
  the `EU`, `US`, `UK`, and `CM` columns.

## Attribution

This library is an independent reimplementation inspired by
[schuhgroessen-umrechner](https://github.com/sarah-schuh/schuhgroessen-umrechner)
by Sarah Amft.

The original project provided the reference for the shoe-size conversion
functionality. The implementation, API, and examples have been completely
rewritten as a standalone library.

All credit for the original work and inspiration goes to
[Sarah Amft](https://github.com/sarah-schuh).

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
