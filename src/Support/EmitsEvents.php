<?php namespace Decahedron\Vulcan\Support;

trait EmitsEvents
{
    protected $eventRegistry = [];

    /**
     * @param string   $event
     * @param callable $callback
     * @return EmitsEvents
     */
    public function on(string $event, callable $callback): self
    {
        if (!array_key_exists($event, $this->eventRegistry)) {
            $this->eventRegistry[$event] = [];
        }
        array_push($this->eventRegistry[$event], $callback);

        return $this;
    }

    /**
     * @param string $event
     * @param array  ...$args
     * @return EmitsEvents
     */
    public function fire(string $event, ... $args): self
    {
        if (array_key_exists($event, $this->eventRegistry)) {
            foreach ($this->eventRegistry[$event] as $callback) {
                call_user_func_array($callback, $args);
            }
        }

        return $this;
    }
}