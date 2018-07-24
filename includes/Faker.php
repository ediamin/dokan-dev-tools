<?php

namespace Dokan\DevTools;

/**
 * Create Faker instance and add providers
 *
 * @since 1.0.0
 */
class Faker {
    /**
     * Class instance object
     *
     * @since 1.0.0
     *
     * @var object
     */
    private static $instance;

    /**
     * Faker object
     *
     * @since 1.0.0
     *
     * @var object
     */
    public $faker;

    /**
     * Class singleton instance
     *
     * Insures that only one instance of `Faker` exists in memory at any one
     * time. Also prevents needing to define globals all over the place.
     *
     * @since 1.0.0
     *
     * @return object
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) && ! ( self::$instance instanceof Faker ) ) {
            self::$instance = new Faker;
            self::$instance->create();
        }

        return self::$instance;
    }

    /**
     * Create the faker instance and add providers
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function create() {
        $this->faker = \Faker\Factory::create();

        $this->faker->addProvider( new \Faker\Provider\en_US\Person($this->faker ) );
        $this->faker->addProvider( new \Faker\Provider\Internet( $this->faker ) );
        $this->faker->addProvider( new \Faker\Provider\Base( $this->faker ) );
        $this->faker->addProvider( new \Faker\Provider\DateTime( $this->faker ) );
        $this->faker->addProvider( new \Faker\Provider\en_US\Address( $this->faker ) );
        $this->faker->addProvider( new \Faker\Provider\en_US\PhoneNumber( $this->faker ) );
        $this->faker->addProvider( new \Faker\Provider\Miscellaneous( $this->faker ) );
    }

    /**
     * Get faker instance
     *
     * @since 1.0.0
     *
     * @return object
     */
    public static function get() {
        return Faker::get_instance()->faker;
    }
}
