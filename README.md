# WB Importer

## Пример выполнения команды

### Без указания сущности (промпт с выбором)

```bash
php artisan app:import --from 2025-07-10
```

### Импорт заказов

```bash
php artisan app:import orders --from=2025-08-01 --to=2025-08-10
```

### Импорт продаж

```bash
php artisan app:import sales --from=2025-08-01 --to=2025-08-10
```

### Импорт складов

```bash
php artisan app:import stocks --date=2025-08-10
```

### Импорт доходов

```bash
php artisan app:import incomes --from=2025-08-01 --to=2025-08-10
```
