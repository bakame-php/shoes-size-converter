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

Create a shoe size using one of the supported units:

```php
use Bakame\Shoes\Converter;
use Bakame\Shoes\Unit;

$euShoeSize = Unit::Eu->size(43);
// $euShoeSize is an instance of Bakame\Shoes\ShoeSize
```

A `ShoeSize` exposes its numeric value and unit:

```php
$euShoeSize->value;
// 43

$euShoeSize->unit;
// Unit::Eu
```

### Calculated conversions

You can calculate an equivalent shoe size in any supported unit using the
foot-length-based conversion formulas:

```php
$ukShoeSize = $euShoeSize->in(Unit::Uk);
// returns a ShoeSize containing the calculated UK size

$ukShoeSize->value;
// 9.5

$ukShoeSize->unit;
// Unit::Uk
```

The calculated value is the nearest size supported by the target sizing
system. It is **not** a lookup in the ISO conversion table.

You can also calculate all supported equivalents at once with
`ShoeSize::equivalents()`:

```php
$result = $euShoeSize->equivalents();
// returns an array containing the calculated equivalent shoe sizes
// for all supported units
```

### Physical measurements

A `ShoeSize` can also be represented as a physical foot measurement.
Dedicated methods are available for millimeters, centimeters, and inches:

```php
$ukShoeSize->inMillimeters();
// 275

$ukShoeSize->inCentimeters();
// 27.5

$ukShoeSize->inInches();
// 10.826771653543
```

These measurements are derived from the calculated foot length and are
independent of the conversion lookup table.

### ISO 19407:2023-based conversions

If you require an exact equivalence from the provided conversion data rather
than a calculated value, use `Converter`.

For example, when using the CSV persistence layer:

```php
$path = __DIR__ . '/data/shoe_sizes.csv';

$converter = Converter::fromCsv($path);
```

`Converter::availableSizes()` returns the sizes available for a specific
unit:

```php
$converter->availableSizes(Unit::Eu);
// returns an iterator of ShoeSize instances
// containing all EU sizes available in the data source
```

You can retrieve all available equivalents for a shoe size using
`Converter::equivalents()`:

```php
$result = $converter->equivalents($euShoeSize);
// returns the equivalent shoe sizes found in the lookup data
```

Or retrieve the equivalent for a specific unit with `Converter::inUnit()`:

```php
$ukShoeSize = $converter->inUnit($euShoeSize, Unit::Uk);
// returns the UK equivalent found in the lookup data
```

Unlike `ShoeSize::in()` and `ShoeSize::equivalents()`, these methods **do not
calculate missing values**. They only return equivalences that are explicitly
present in the configured persistence layer.

For example, a size may have no exact equivalent in the lookup data even
though a calculated equivalent can be produced.

Please refer to the [Persistence Layer](#persistence-layer) section for
information about the supported persistence layers and how to create and
store the conversion data.

## HTTP Endpoint

> [!WARNING]
>  **the HTTP endpoint is only available when the package has been cloned.**

The original script by Sarah Amft has been reimplemented using this library's API
in the `api` directory. It exposes the conversion functionality as an HTTP
API endpoint that returns JSON responses. See [converter.php](api/converter.php).

To run the converter:

- **clone this repo**, 
- go to the root directory,
- and start PHP's built-in development server

```bash
php -S localhost:4000
````

Then open your browser or HTTP client and request:

```text
http://localhost:4000/api/converter.php?unit=EU&size=42.5
```

This returns:

```json
{"source":"calculated", "sizes":[{"value":270,"unit":"mondopoint"},{"value":27,"unit":"cm"},{"value":42.5,"unit":"eu"},{"value":9,"unit":"uk"},{"value":10,"unit":"us_men"},{"value":11,"unit":"us_women"}],"measurements":{"centimeters":27,"inches":10.62992125984252}}
```

in pretty print it gives:

```json
{
    "source": "calculated",
    "sizes": [
        {
            "value": 270,
            "unit": "mondopoint"
        },
        {
            "value": 27,
            "unit": "cm"
        },
        {
            "value": 42.5,
            "unit": "eu"
        },
        {
            "value": 9,
            "unit": "uk"
        },
        {
            "value": 10,
            "unit": "us_men"
        },
        {
            "value": 11,
            "unit": "us_women"
        }
    ],
    "measurements": {
        "centimeters": 27,
        "inches": 10.6299212598425
    }
}
```

## CLI command

> [!TIP]
>  **the CLI command is always available.**

```bash
vendor/bin/shoe-converter EU 42.5
```

This returns:

```bash
Shoe size conversion
Input
  eu 42.5
Sizes (calculated)
  mondopoint 270
  cm 27
  eu 42.5
  uk 9
  us_men 10
  us_women 11
Measurements
  27 cm
  10.629921259843 in
```

## Persistence Layer

The package relies on the `League\Csv\TabularData` interface for reading
shoe-size data. You can load data from either a CSV file or a database table.

Both sources must follow the expected shoe-size structure documented by the
corresponding factory methods:

- `fromCsv()` — loads data from a CSV file whose first row contains the
  `eu`, `us_men`, `us_women`, `uk`, `cm` and `mondopoint` column headers.
- `fromPdo()` — loads data from a `shoe_sizes` database table containing
  the   `eu`, `us_men`, `us_women`, `uk`, `cm` and `mondopoint` columns.

> [!IMPORTANT]
> The conversion table in the `data` directory is provided in `CSV` and `SQLite`
> formats and contains **adult shoe-size conversions based on ISO 19407:2023**.

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
