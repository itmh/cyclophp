# cyclophp

`cyclophp` is a tool calculating cyclomatic complexity of public methods

## Installation

### Composer

Simply add a dependency on `itmh/cyclophp` to your project's `composer.json` file if you use [Composer](http://getcomposer.org/) to manage the dependencies of your project. 
Here is a minimal example of a `composer.json` file that just defines a development-time dependency on Cyclophp:

    {
        "require-dev": {
            "itmh/cyclophp": "*"
        }
    }

For a system-wide installation via Composer, you can run:

    composer global require 'itmh/cyclophp=*'

Make sure you have `~/.composer/vendor/bin/` in your path.

## Usage Examples

### Analyse a directory and print the result

    $ cyclophp run src
     6/6 [============================] 100%
     10/10 [============================] 100%
    +-----------------------------------+------------+
    | Method                            | Complexity |
    +-----------------------------------+------------+
    | Cyclophp\SourceExtractor::extract | 3          |
    | Cyclophp\Sorter::sort             | 2          |
    | Cyclophp\ComplexityCounter::count | 2          |
    +-----------------------------------+------------+

### Analyse with parameters

    $ cyclophp run src --threshold=3 --public-only=no
     6/6 [============================] 100%
     22/22 [============================] 100%
    +-----------------------------------------+------------+
    | Method                                  | Complexity |
    +-----------------------------------------+------------+
    | Cyclophp\SourceExtractor::extractMethod | 4          |
    | Cyclophp\SourceExtractor::parse         | 4          |
    | Cyclophp\RunCommand::results            | 3          |
    | Cyclophp\ComplexityCounter::method      | 3          |
    | Cyclophp\SourceExtractor::extract       | 3          |
    +-----------------------------------------+------------+
