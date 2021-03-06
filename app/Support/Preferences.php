<?php
/**
 * Preferences.php
 * Copyright (C) 2016 thegrumpydictator@gmail.com
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

declare(strict_types = 1);

namespace FireflyIII\Support;

use Cache;
use FireflyIII\Models\Preference;
use FireflyIII\User;

/**
 * Class Preferences
 *
 * @package FireflyIII\Support
 */
class Preferences
{
    /**
     * @param $name
     *
     * @return bool
     * @throws \Exception
     */
    public function delete($name): bool
    {
        $fullName = 'preference' . auth()->user()->id . $name;
        if (Cache::has($fullName)) {
            Cache::forget($fullName);
        }
        Preference::where('user_id', auth()->user()->id)->where('name', $name)->delete();

        return true;
    }

    /**
     * @param      $name
     * @param null $default
     *
     * @return \FireflyIII\Models\Preference|null
     */
    public function get($name, $default = null)
    {
        $user = auth()->user();
        if (is_null($user)) {
            return $default;
        }

        return $this->getForUser(auth()->user(), $name, $default);
    }

    /**
     * @param \FireflyIII\User $user
     * @param      string      $name
     * @param string           $default
     *
     * @return \FireflyIII\Models\Preference|null
     */
    public function getForUser(User $user, $name, $default = null)
    {
        $fullName = 'preference' . $user->id . $name;
        if (Cache::has($fullName)) {
            return Cache::get($fullName);
        }

        $preference = Preference::where('user_id', $user->id)->where('name', $name)->first(['id', 'name', 'data']);

        if ($preference) {
            Cache::forever($fullName, $preference);

            return $preference;
        }
        // no preference found and default is null:
        if (is_null($default)) {
            // return NULL
            return null;
        }

        return $this->setForUser($user, $name, $default);

    }

    /**
     * @return string
     */
    public function lastActivity(): string
    {
        $preference = $this->get('lastActivity', microtime())->data;

        return md5($preference);
    }

    /**
     * @return bool
     */
    public function mark(): bool
    {
        $this->set('lastActivity', microtime());

        return true;
    }

    /**
     * @param        $name
     * @param string $value
     *
     * @return Preference
     */
    public function set($name, $value): Preference
    {
        $user = auth()->user();
        if (is_null($user)) {
            // make new preference, return it:
            $pref       = new Preference;
            $pref->name = $name;
            $pref->data = $value;

            return $pref;
        }

        return $this->setForUser(auth()->user(), $name, $value);
    }

    /**
     * @param \FireflyIII\User $user
     * @param                  $name
     * @param string           $value
     *
     * @return Preference
     */
    public function setForUser(User $user, $name, $value): Preference
    {
        $fullName = 'preference' . $user->id . $name;
        Cache::forget($fullName);
        $pref = Preference::where('user_id', $user->id)->where('name', $name)->first(['id', 'name', 'data']);

        if (!is_null($pref)) {
            $pref->data = $value;
            $pref->save();

            Cache::forever($fullName, $pref);

            return $pref;
        }

        $pref       = new Preference;
        $pref->name = $name;
        $pref->data = $value;
        $pref->user()->associate($user);

        $pref->save();

        Cache::forever($fullName, $pref);

        return $pref;

    }
}
