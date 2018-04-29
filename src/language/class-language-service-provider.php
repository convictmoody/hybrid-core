<?php
/**
 * Language service provider.
 *
 * This is the service provider for the language system, which binds an instance
 * of the framework's `Language` class to the container.
 *
 * @package   HybridCore
 * @author    Justin Tadlock <justintadlock@gmail.com>
 * @copyright Copyright (c) 2008 - 2018, Justin Tadlock
 * @link      https://themehybrid.com/hybrid-core
 * @license   http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 */

namespace Hybrid\Language;

use Hybrid\Core\ServiceProvider;

/**
 * Language provider.
 *
 * @since  5.0.0
 * @access public
 */
class LanguageServiceProvider extends ServiceProvider {

        /**
         * Registration callback that adds a single instance of the language
         * system to the container.
         *
         * @since  5.0.0
         * @access public
         * @return void
         */
        public function register() {

                $this->app->instance( 'language', new Language() );
        }
}