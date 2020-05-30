# One command to find missing translations in Laravel application

Laravel artisan command to list all your untranslated words. 

## Prerequisites

There is no prerequisites for this command to work, you just need to follow the installing process.

## Installing

Installing this command is easy, you can use composer or do it manually :
### Composer

```
composer require veton-muhaxhiri/laravel-command-to-find-missing-translations
```
By running composer command you will have everything setup and ready to be used.

### Manually
1. git clone the repository, or just download it.
2. copy "src/Commands/FindMissingTranslations.php" file to /app/Console/Commands of your laravel project.

By completing this steps the command should work, unless the command is not loaded automaticly.
To load the command go to app/Console/Kernel.php and add the class name to the $commands property.

```
protected $commands = [
    Commands\FindMissingTranslations::class
];
```
### Lumen
In Laravel Lumen we just need one extra step, go open ```bootstrap/app.php``` file and register the service provider by adding the line
```$app->register(VetonMuhaxhiri\Laravelfindmissingtranslations\Providers\FindMissingTranslationsProvider::class);```

## Running the command

The command expects two arguments :
1. **language directory** - Relative path of language directory for ex. /resources/lang is a directory that contains all supported language in your laravel app.
2. **base language** - Base language for ex. "en". All other languages are compared to this language.

Inside of **language directory**  
![Language directory](https://i.imgur.com/eXGlUI8.png)

While **base language** should be one of the language listed in the picture.

### Example
```
$ php artisan translations:missing /resources/lang en
```
![Proof of concept](https://imgur.com/PNxv82D.png)

## Features
#### Recursive
Detects missing words in multilevel array for ex.  
![Multilevel array](https://imgur.com/Hn4YQB7.png)

#### Missing files
Detects missing files, for ex. if file with translation named "posts.php" exists in english but not in deutsch.
## Authors

* **Veton Muhaxhiri** - [LinkedIn](https://www.linkedin.com/in/veton-muhaxhiri-815113196)



