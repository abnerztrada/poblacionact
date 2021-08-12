<?php
namespace tool_poblacion\task;

use tool_poblacion\models\correo;
use tool_poblacion\models\report;
class poblacion_inscrita extends \core\task\scheduled_task
{
    /**
     * return name of task for admin panel.
     *
     * @return string name
     */
    public function get_name()
    {
        return get_string('cronenroll', 'tool_poblacion');
    }

    /**
     * method to execute by cron task.
     */
    public function execute()
    {
      // mtrace("Hola mundo");
      global $CFG;
      $correo_envio = new correo();
      $correo_envio->correo_envio();
    }
}
