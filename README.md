# PHP-Scoper

[![Package version](https://img.shields.io/packagist/v/humbug/php-scoper.svg?style=flat-square)](https://packagist.org/packages/humbug/php-scoper)
[![Travis Build Status](https://img.shields.io/travis/humbug/php-scoper.svg?branch=master&style=flat-square)](https://travis-ci.org/humbug/php-scoper?branch=master)
[![AppVeyor Build Status](https://img.shields.io/appveyor/ci/humbug/php-scoper.svg?branch=master&style=flat-square)](https://ci.appveyor.com/project/humbug/php-scoper/branch/master)
[![Scrutinizer Code Quality](https://img.shields.io/scrutinizer/g/humbug/php-scoper.svg?branch=master&style=flat-square)](https://scrutinizer-ci.com/g/humbug/php-scoper/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/humbug/php-scoper/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/humbug/php-scoper/?branch=master)
[![Slack](https://img.shields.io/badge/slack-%23humbug-red.svg?style=flat-square)](https://symfony.com/slack-invite)
[![License](https://img.shields.io/badge/license-MIT-red.svg?style=flat-square)](LICENSE)

PHP-Scoper is a tool which essentially moves any body of code, including all
dependencies such as vendor directories, to a new and distinct namespace.

## Goal

PHP-Scoper's goal is to make sure that all code for a project lies in a 
distinct PHP namespace. This is necessary, for example, when building PHARs that:

* Bundle their own vendor dependencies; and
* Load/execute code from arbitrary PHP projects with similar dependencies

These PHARs run the risk of raising conflicts between their bundled vendors and 
the vendors of the project it is interacting with, where the PHAR's dependencies
are used preferentially leading to difficult to debug issues due to dissimilar or
unsupported package versions.

## Installation

The preferred method of installation is to use the PHP-Scoper phar, which can
be downloaded from the most recent Github Release. Subsequent updates can be
downloaded by running:

```bash
php-scoper.phar self-update
```

As the PHAR is signed, you should also download the matching
`php-scoper.phar.pubkey` to the same location. If you rename `php-scoper.phar`
to `php-scoper`, you should also rename `php-scoper.phar.pubkey` to
`php-scoper.pubkey`.

## Usage

```bash
php-scoper add-prefix
```

This will prefix all relevant namespaces in code found in the current working
directory. The prefixed files will be accessible in a `build` folder. You can
then use the prefixed code to build your PHAR.

**Warning**: After prefixing the files, if you are relying on Composer
for the autoloading, dumping the autoloader again is required.

For a more concrete example, you can take a look at PHP-Scoper's build
step in [Makefile](Makefile), especially if you are using Composer as
there are steps both before and after running PHP-Scoper to consider.

Refer to TBD for an in-depth look at scoping and building a PHAR taken from
PHP-Scoper's makefile.

## Patchers

When scoping PHP files, there will be scenarios where some of the code being
scoped indirectly references the original namespace. These will include, for
example, strings or string manipulations. PHP-Scoper has limited support for
prefixing such strings, so you may need to define `patchers`, one or more
callables in a `scoper.inc.php` configuration file which can be used to replace
some of the code being scoped.

Here's a simple example:

* Class names in strings.

You can imagine instantiating a class from a variable which is based on a
known namespace, but also on a variable classname which is selected at
runtime. Perhaps code similar to:

```php
$type = 'Foo'; // determined at runtime
$class = 'Humbug\\Format\\Type\\' . $type;
```

If we scoped the `Humbug` namespace to `PhpScoperABC\Humbug`, then the above
snippet would fail as PHP-Scoper cannot interpret the above as being a namespaced
class. To complete the scoping successfully, a) the problem must
be located and b) the offending line replaced.

The patched code which would resolve this issue might be:

```php
$type = 'Foo'; // determined at runtime
$scopedPrefix = array_shift(explode('\\', __NAMESPACE__));
$class = $scopedPrefix . '\\Humbug\\Format\\Type\\' . $type;
```

This and similar issues *may* arise after scoping, and can be debugged by
running the scoped code and checking for issues. For this purpose, having a
couple of end to end tests to validate post-scoped code or PHARs is recommended.

Applying such a change can be achieved by defining a suitable patcher in
`scoper.inc.php`:

```php
return [
    'patchers' => [
        function (string $filePath, string $prefix, string $content): string {
            //
            // PHP-Parser patch
            //
            if ($filePath === '/path/to/offending/file') {
                return preg_replace(
                    "%\$class = 'Humbug\\\\Format\\\\Type\\\\' . \$type;%",
                    '$class = $scopedPrefix . \'\\\\Humbug\\\\Format\\\\Type\\\\\' . $type;',
                    $content
                );
            }
            return $content;
        },
    ],
];
```

## Global Namespace Whitelisting

By default, PHP-Scoper only scopes (or prefixes) code where the namespace is
non-global. In other words, non-namespaced code is not scoped. This leaves the
majority of classes, functions and constants in PHP, and most extensions,
untouched.

This is not necessarily a desireable outcome for vendor dependencies which are
also not namespaced. To ensure they are isolated, you can configure PHP-Scoper to
allow their prefixing from `scoper.inc.php` using basic strings or callables:

```php
return [
    'global_namespace_whitelist' => [
        'AppKernel',
        function ($className) {
            return 'PHPUnit' === substr($className, 0, 6);
        },
    ],
    'patchers' => [
        // patchers if relevant
    ]
]
```

In this example, we're ensuring that the `AppKernal` class, and any
non-namespaced PHPUnit packages are prefixed.

## Contributing

[Contribution Guide](CONTRIBUTING.md)

## Building A Scoped PHAR

This is a brief run through of the steps encoded in PHP-Scoper's own
[Makefile](Makefile) and elsewhere to build a PHAR from scoped code.

###Step 1: Configure building the PHAR from `./build`

If, for example, you are using [Box](box) to build your PHAR, you
should set the `base-path` configuration option in your `box.json` file
to point at the 'build' directory which will host scoped code.

```js
"base-path": "build"
```

###Step 2:

TBD
TBD
TBD

## Credits

Project originally created by: [Bernhard Schussek] ([@webmozart]) which has
now been moved under the
[Humbug umbrella][humbug].


[Bernhard Schussek]: https://webmozart.io/
[@webmozart]: https://twitter.com/webmozart
[humbug]: https://github.com/humbug
[bamarni/composer-bin-plugin]: https://github.com/bamarni/composer-bin-plugin
[box]: https://github.com/box-project/box2
