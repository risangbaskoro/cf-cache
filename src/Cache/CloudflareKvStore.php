<?php

namespace Baskoro\CloudflareCache\Cache;

use Baskoro\CloudflareCache\Cache\Requests\DeleteKeyValuePair;
use Baskoro\CloudflareCache\CloudflareConnector;
use Baskoro\CloudflareCache\CloudflareKvException;
use Carbon\Carbon;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Support\Collection;
use Illuminate\Support\InteractsWithTime;
use Illuminate\Support\Str;
use RuntimeException;

class CloudflareKvStore implements Store
{
    use InteractsWithTime;

    /**
     * The Cloudflare connector instance.
     */
    protected CloudflareConnector $connector;

    /**
     * A string that should be prepended to keys.
     */
    protected string $prefix;

    public function __construct(
        CloudflareConnector $connector,
        string $prefix = '',
    ) {
        $this->connector = $connector;

        $this->setPrefix($prefix);
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @return mixed
     */
    public function get($key)
    {
        try {
            $response = $this->connector
                ->send(new Requests\ReadKeyValuePair($this->getPrefix().$key))
                ->throw();

            return $this->unserialize($response->body());
        } catch (CloudflareKvException $e) {
            if (str_contains($e->getMessage(), 'key not found')) {
                return null;
            }

            throw $e;
        }
    }

    /**
     * Retrieve multiple items from the cache by key.
     *
     * Items not found in the cache will have a null value.
     *
     * @return array
     */
    public function many(array $keys)
    {
        if (count($keys) === 0) {
            return [];
        }

        $prefixedKeys = array_map(function ($key) {
            return $this->getPrefix().$key;
        }, $keys);

        $response = $this->connector
            ->send(new Requests\ReadMultipleKeyValuePairs($prefixedKeys));

        return array_merge((new Collection(array_flip($keys)))->map(function () {
            //
        })->all(), (new Collection($response->json('result.values')))->mapWithKeys(function ($value, $key) {
            return [Str::replaceFirst($this->getPrefix(), '', $key) => $this->unserialize($value)];
        })->all());
    }

    /**
     * Store an item in the cache for a given number of seconds.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @param  int  $seconds
     * @return bool
     */
    public function put($key, $value, $seconds)
    {
        $response = $this->connector
            ->send(new Requests\WriteKeyValuePair(
                $this->getPrefix().$key,
                $this->serialize($value),
                $seconds,
            ));

        return $response->successful();
    }

    /**
     * Store multiple items in the cache for a given number of seconds.
     *
     * @param  int  $seconds
     * @return bool
     */
    public function putMany(array $values, $seconds)
    {
        if (count($values) === 0) {
            return true;
        }

        $expiration = $this->toTimestamp($seconds);

        $response = $this->connector
            ->send(new Requests\WriteMultipleKeyValuePairs(
                (new Collection($values))
                    ->map(function ($value, $key) use ($expiration) {
                        return [
                            'key' => $this->getPrefix().$key,
                            'value' => $this->serialize($value),
                            'expiration' => $expiration,
                        ];
                    })->values()->all()
            ));

        return empty($response->json('result.unsuccessful_keys'));
    }

    /**
     * Increment the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return int|bool
     *
     * @throws RuntimeException
     */
    public function increment($key, $value = 1)
    {
        throw new RuntimeException('Cloudflare KV does not support atomic counters.', 1);
    }

    /**
     * Decrement the value of an item in the cache.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return int|bool
     *
     * @throws RuntimeException
     */
    public function decrement($key, $value = 1)
    {
        throw new RuntimeException('Cloudflare KV does not support atomic counters.', 1);
    }

    /**
     * Store an item in the cache indefinitely.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return bool
     */
    public function forever($key, $value)
    {
        $now = Carbon::now();

        return $this->put($key, $value, (int) Carbon::now()->addYears(5)->diffInSeconds($now, true));
    }

    /**
     * Remove an item from the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function forget($key)
    {
        return $this->connector
            ->send(new DeleteKeyValuePair($this->getPrefix().$key))
            ->successful();
    }

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush()
    {
        throw new RuntimeException('Cloudflare does not support flushing an entire namespace. Please create a new namespace.');
    }

    /**
     * Get the UNIX timestamp for the given number of seconds.
     *
     * @param  int  $seconds
     * @return int
     */
    protected function toTimestamp($seconds)
    {
        return $seconds > 0
            ? $this->availableAt($seconds)
            : $this->currentTime();
    }

    /**
     * Serialize the value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function serialize($value)
    {
        return is_numeric($value) ? (string) $value : serialize($value);
    }

    /**
     * Unserialize the value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        if (filter_var($value, FILTER_VALIDATE_INT) !== false) {
            return (int) $value;
        }

        if (is_null($value)) {
            return $value;
        }

        if (is_numeric($value)) {
            return (float) $value;
        }

        return unserialize($value);
    }

    /**
     * Get the cache key prefix.
     *
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Set the cache key prefix.
     *
     * @param  string  $prefix
     * @return void
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Get the Cloudflare Connector instance.
     *
     * @return CloudflareConnector
     */
    public function getConnector()
    {
        return $this->connector;
    }
}
