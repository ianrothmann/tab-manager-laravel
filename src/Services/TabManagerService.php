<?php

namespace StianScholtz\TabManager\Services;

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
     * @param string|int|null $key
     * @param mixed $value
     */
    public function set($key, $value): void
    {
        //Check for tabId and not tab. tab will be empty array on initial requests
        if ($tabId = $this->current()) {
            $tab = $this->get();
            Arr::set($tab, $key, $value);
            session()->put($this->key . '.tabs.' . $tabId, $tab);
        }
    }

    /**
     * @param string|array $key
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
        $tabs = session($this->key . '.tabs', []);

        if (! $tabs || count($tabs) === 0) {
            return null;
        }

        return collect($tabs)
            ->sortByDesc(function ($tab) {
                return $tab['last_accessed'];
            })
            ->first(function ($tab) {
                $path = pathinfo($tab['url']);

                return request()->is($path);
            });
    }

    private function setDefaultSession(): void
    {
        $parent = $this->getLatestForCurrentURL();
        $defaultSession = $parent ?? [];
        session()->put($this->key . '.tabs.' . $this->current(), $defaultSession);
    }

    private function touch(): void
    {
        $this->set('last_accessed', now()->toDateTimeString());
        $this->set('prev_url', $this->get('url'));
        $this->set('url', request()->url());
    }
}
