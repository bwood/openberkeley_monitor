<?php
/**
 * @file
 * Contains \Drupal\monitoring\Sensor\Sensors\SensorFeaturesOverrides.
 */

namespace Drupal\openberkeley_monitor\Sensor\Sensors;

use Drupal\monitoring\Result\SensorResultInterface;
use Drupal\monitoring\Sensor\Sensor;

/**
 * Monitors for available updates of Drupal core and installed contrib modules.
 *
 * Based on drupal core update module.
 */
class SensorFeaturesOverrides extends Sensor {

  /**
   * Runs the sensor, updating $sensor_result.
   *
   * An implementation must provide any checks necessary to be able to populate
   * the provides sensor result instance with a combination of the following
   * possibilities:
   *
   *  - Set the sensor status to critical, warning, ok, info or unknown with
   *    SensorResultInterface::setStatus(). Defaults to unknown.
   *  - Set the sensor value with SensorResultInterface::setValue(). This can
   *    be a number or a string. Note that hook_monitoring_sensor_info()
   *    defaults to numeric. If a sensor does not return a numeric result,
   *    it must be defined accordingly.
   *  - Set the expected sensor value with SensorResultInterface::setExpectedValue().
   *    When doing so, it is not necessary to set the sensor status explicitly,
   *    as that will happen implicitly. See below.
   *  - Set the sensor message with SensorResultInterface::setMessage(), which
   *    will then be used as is. The message must include all relevant
   *    information.
   *  - Add any number of status messages which will then be added to the
   *    final sensor message.
   *
   * Based on the provided information, the sensor result will then be compiled.
   * It will attempt to set the sensor status if not already
   * done explicitly by the sensor and will build a default message, unless a
   * message was already set with SensorResultInterface::setMessage().
   *
   * Sensors with unknown status can either be set based on an expected value or
   * thresholds. If the value does not match the expected value, the status
   * is set to critical. Sensors that support thresholds should either subclass
   * \Drupal\monitoring\Sensor\SensorThresholds or implement
   * \Drupal\monitoring\SensorThresholdsInterface and provide their own
   * configuration form.
   *
   * The default sensor message will include information about the sensor value,
   * expected value, thresholds, the configured time interval and additional
   * status messages defined.
   * Provided value labels and value types will be considered for displaying the
   * sensor value, see hook_monitoring_sensor_info() for their documentation. If
   * neither value nor status messages are provided, the message will default to
   * "No value".
   *
   * Compiled message examples:
   *  - $90.00 in 1 day, expected $100.00.
   *    This is the message for a sensor with a commerce_currency value type, a
   *    configured time interval of one day and a value of 90 and expected value
   *    of 100.
   *  - 53 login attempts in 6 hours, exceeds 20, 10 for user administrator.
   *    This the message for a failed login sensor with value 53 with a
   *    threshold configuration of exceeds 20 and a status message "10 for user
   *    administrator".
   *
   * @param SensorResultInterface $sensor_result
   *   Sensor result object.
   *
   * @throws \Exception
   *   Can throw any exception. Must be caught and handled by the caller.
   *
   * @see \Drupal\monitoring\Result\SensorResultInterface::setValue()
   * @see \Drupal\monitoring\Result\SensorResultInterface::setExpectedValue()
   * @see \Drupal\monitoring\Result\SensorResultInterface::compile()
   *
   * @see \Drupal\monitoring\Result\SensorResultInterface::setMessage()
   * @see \Drupal\monitoring\Result\SensorResultInterface::addStatusMessage()
   */
  function runSensor(SensorResultInterface $sensor_result) {

    module_load_include('inc', 'features', 'features.export');

    // Sort the Features list before compiling the output.
    $features = features_get_features(NULL, TRUE);
    ksort($features);

    $overridden = array();
    $needs_review = array();
    foreach ($features as $k => $m) {
      /*
          $rows[$k] = array(
          'name' => $m->info['name'],
          'feature' => $m->name,
          'status' => $m->status ? dt('Enabled') : dt('Disabled'),
          'version' => $m->info['version'],
          'state' => $storage
        );

      */
      if ($m->status != 1 ) {
        // Don't consider disabled features.
        continue;
      }

      switch (features_get_storage($m->name)) {
        //case FEATURES_DEFAULT:
        //case FEATURES_REBUILDABLE:
        //  break;
        case FEATURES_OVERRIDDEN:
          $overridden[] = $m->name;
          break;
        case FEATURES_NEEDS_REVIEW:
          $needs_review[] = $m->name;
          break;
      }
    }

    //TODO: needs review
    if (count($overridden)) {
      $sensor_result->addStatusMessage('Overridden features: @overridden', array('@overridden' => implode(', ', $overridden)));
    }
    else {
      $sensor_result->addStatusMessage("No features in overridden state.");
    }

  }
}

/*
          $rows[$k] = array(
          'name' => $m->info['name'],
          'feature' => $m->name,
          'status' => $m->status ? dt('Enabled') : dt('Disabled'),
          'version' => $m->info['version'],
          'state' => $storage
        );

 */

