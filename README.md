# MoonShine Import/Export Handlers

[Documentation](https://moonshine-laravel.com/docs/3.x/model-resource/import-export)

## Requirements

- MoonShine 3+
- Laravel 10+
- PHP 8.2+

## Installation

```shell
composer require moonshine/import-export
```

## Usage

* Add `trait` `MoonShine\ImportExport\Traits\ImportExportConcern` and `interface` `MoonShine\ImportExport\Contracts\HasImportExportContract` to ModelResource

```php
/**
 * @extends ModelResource<Category>
 */
class CategoryResource extends ModelResource implements HasImportExportContract
{
    use ImportExportConcern;
    
    // ...
}
```

* Define fields

```php
/**
 * @extends ModelResource<Category>
 */
class CategoryResource extends ModelResource implements HasImportExportContract
{
    use ImportExportConcern;
    
    // ...
    
    protected function exportFields(): iterable
    {
        return [
            ID::make(),
            Position::make(),
            Text::make('Name'),
        ];
    }
    
    protected function importFields(): iterable
    {
        return [
            ID::make(),
            Text::make('Name'),
        ];
    }
}
```

* Events

```php
public function beforeImportFilling(array $data): array
{
    return $data;
}

public function beforeImported(mixed $item): mixed
{
    return $item;
}

public function afterImported(mixed $item): mixed
{
    return $item;
}
```

* Queue

```php
protected function export(): ?Handler
{
    return ExportHandler::make(__('moonshine::ui.export'))->queue();
}

protected function import(): ?Handler
{
    return ImportHandler::make(__('moonshine::ui.import'))->queue();
}
```


