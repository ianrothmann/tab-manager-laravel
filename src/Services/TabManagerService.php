<?php

namespace Eawardie\TabManager\Services;

use Illuminate\Session\SessionManager;
use Illuminate\Session\Store;
use Illuminate\Support\Arr;

class TabManagerService
{
    private string $key;
    private ?string $tabId;

    public function __construct()
    {
        $this->key = config('tab-manager.session.key', 'tab_manager');
    }

    private function __clone()
    {
        //Not allowed to be cloned
    }

    /**
     * @param null $key
     * @return string
     */
    private function getKey($key = null)
    {
        return rtrim($this->key . '.tabs.' . $this->current() . '.' . $key, '.');
    }

    /**
     * @return string|null
     */
    private function setTabId(): ?string
    {
        $this->tabId = request()->cookie('tab_id');

        return $this->tabId;
    }

    private function updateSession(): void
    {
        if ($this->current()) {
            //If there is a session for the current tabId then we do not need to set a default session.
            $exists = $this->has();

            if (! $exists) {
                $this->setDefaultSession();
            }

            $this->touch();
        }
    }

    public function check(): ?string
    {
        $tabId = $this->setTabId();
        $this->updateSession();

        return $tabId;
    }

    /**
     * @return string|null
     */
    public function current(): ?string
    {
        return $this->tabId;
    }

    /**
     * @param null $key
     * @return SessionManager|Store|mixed|null
     */
    public function get($key = null)
    {
        if (! ($tabId = $this->current())) {
            return null;
        }

        return session($this->getKey($key), null);
    }

    /**
     * @param array|string|int $key
     * @param mixed $value
     */
    public function set($key, $value = null): void
    {
        //Check for $tabId and not tab because $tab will be empty array on initial requests
        if ($tabId = $this->current()) {
            $tab = $this->get();

            if (! is_array($key)) {
                $key = [$key => $value];
            }

            foreach ($key as $arrayKey => $arrayValue) {
                Arr::set($tab, $arrayKey, $arrayValue);
            }

            session()->put($this->key . '.tabs.' . $tabId, $tab);
        }
    }

    /**
     * @param string|array|null $key
     * @return bool
     */
    public function has($key = null): bool
    {
        if (! ($tabId = $this->current())) {
            return false;
        }

        return session()->has($this->getKey($key));
    }

    /**
     * @return array|null
     */
    public function getLatestForCurrentURL(): ?array
    {
        return $this->getLatestByPath(request()->path() ?? '');
    }

    /**
     * @return array|null
     */
    public function getLatestForPrevUrl(): ?array
    {
        $prevUrl = \URL::previous();

        if (! $prevUrl || ! ($path = parse_url($prevUrl, PHP_URL_PATH) ?? '')) {
            return null;
        }

        return $this->getLatestByPath($path);
    }

    private function getLatestByPath(string $path): ?array
    {
        $tabs = session($this->key . '.tabs', []);

        if (! $tabs || count($tabs) === 0) {
            return null;
        }

        $path = trim($path, '/');

        return collect($tabs)
            ->sortByDesc(function ($tab) {
                return $tab['last_accessed_at'];
            })
            ->first(function ($tab) use ($path) {
                $urlPath = parse_url($tab['url'], PHP_URL_PATH);

                if (! $urlPath) {
                    return $path === '';
                }

                return $path === trim($urlPath, '/');
            });
    }

    private function setDefaultSession(): void
    {
        $parent = $this->getLatestForCurrentURL() ?? $this->getLatestForPrevUrl();
        $current = $this->current();
        $defaultSession = $parent ?? [];
        $defaultSession['tab_id'] = $current;
        session()->put($this->key . '.tabs.' . $current, $defaultSession);
    }

    private function touch(): void
    {
        $this->set('last_accessed_at', now()->toDateTimeString());
        $this->set('url', request()->url());
    }
}
