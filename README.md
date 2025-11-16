# Laravel Cloudflare KV Cache Driver

A Laravel cache driver that uses Cloudflare KV as a distributed storage backend.

>[!Warning]
> Proof of concept. Not production-ready. Use with your own risk.

## Why This Exists

I just got bored, I guess.

## Installation

Clone this repository to a custom directory (e.g, `custom_modules`), and add a custom repository in your project's `composer.json` file as follows:

```json
"repositories": [
	{
		"type": "path",
		"url": "custom_modules/cf-cache"
	},
],
```

And add the `require` package dependencies:

```json
"require": {
	...
	"baskoro/cf-cache": "*"
	...
}
```

You might want to change the `minimum-stability` to `dev` in the `composer.json` file.

## Configuration

In your `cache.php` config file, under the `stores` key, append the following config:

```php
'cloudflare' => [
	'driver' => 'cloudflare',
	'token' => env('CF_TOKEN'),
	'account_id' => env('CF_ACCOUNT_ID'),
	'namespace_id' => env('CF_KV_NAMESPACE'),
],
```

Of course, you need to add `CF_TOKEN`, `CF_ACCOUNT_ID`, and `CF_KV_NAMESPACE` to your `.env` file.

## Usage

You may update your `.env` file to use the `cloudflare` driver, or alternatively use the `Cache::store('cloudflare')` method to use the driver on-demand.

Usage is the same as Laravel's Cache. See the [Laravel's Cache Documentation](https://laravel.com/docs/cache).

Examples:

```php
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;

$res = Cache::remember('foo', now()->addMinute(), function () {
	// Simulate long computation
	sleep(1);

	return User::firstWhere('email', 'contact@risangbaskoro.com');
});

echo $res;
```

## Limitations

- Cloudflare KV only accepts storing TTL longer than 60 seconds. [Docs](https://developers.cloudflare.com/kv/api/write-key-value-pairs/#expiring-keys).