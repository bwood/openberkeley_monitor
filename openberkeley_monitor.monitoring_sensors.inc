<?php
/**
 * @file
 * Open Berkeely Sensors
 */

/**
 * Implementation of hookmonitoring_sensor_info().
 */
function openberkeley_monitor_monitoring_sensor_info() {

  $info = array();

  $info['features_overrides'] = array(
    'label' => 'Features overrides status',
    'description' => 'Detected overrides of installed features',
    //'sensor_class' => 'Drupal\openberkeley_monitor\Sensor\Sensors\SensorFeaturesOverrides',
    'sensor_class' => 'Drupal\openberkeley_monitor\Sensor\Sensors\SensorFeaturesOverrides',
    'numeric' => FALSE,
    'value_label' => 'Overridden Features',
    'settings' => array(
      'category' => 'Features',
      // Cache for 1 day.
      //'caching_time' => 24 * 3600,
      //'type' => 'core',
    ),
  );


  return $info;

}