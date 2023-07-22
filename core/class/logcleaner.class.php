<?php
/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once __DIR__  . '/../../../../core/php/core.inc.php';

class logcleaner extends eqLogic {
  const ROOT_FILENAME = 'zLogCleaner_';

  /*     * *************************Attributs****************************** */

  /*
  * Permet de définir les possibilités de personnalisation du widget (en cas d'utilisation de la fonction 'toHtml' par exemple)
  * Tableau multidimensionnel - exemple: array('custom' => true, 'custom::layout' => false)
  public static $_widgetPossibility = array();
  */

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration du plugin
  * Exemple : "param1" & "param2" seront cryptés mais pas "param3"
  public static $_encryptConfigKey = array('param1', 'param2');
  */

  /*     * ***********************Methode static*************************** */

  /*
  * Fonction exécutée automatiquement toutes les minutes par Jeedom
  */
  // public static function cron() {
  //   log::add(__CLASS__, 'debug', " **** cron ****");

  //   logcleaner::execute();
  // }

  /*
  * Fonction exécutée automatiquement toutes les 5 minutes par Jeedom
  public static function cron5() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 10 minutes par Jeedom
  public static function cron10() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 15 minutes par Jeedom
  public static function cron15() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les 30 minutes par Jeedom
  public static function cron30() {}
  */

  /*
  * Fonction exécutée automatiquement toutes les heures par Jeedom
  public static function cronHourly() {}
  */

  /*
  * Fonction exécutée automatiquement tous les jours par Jeedom
  */
  public static function cronDaily() {
    log::add(__CLASS__, 'debug', " **** cronDaily ****");
    logcleaner::execute();
  }


  /*     * *********************Méthodes d'instance************************* */

  // Fonction exécutée automatiquement avant la création de l'équipement
  public function preInsert() {
  }

  // Fonction exécutée automatiquement après la création de l'équipement
  public function postInsert() {
  }

  // Fonction exécutée automatiquement avant la mise à jour de l'équipement
  public function preUpdate() {
  }

  // Fonction exécutée automatiquement après la mise à jour de l'équipement
  public function postUpdate() {
  }

  // Fonction exécutée automatiquement avant la sauvegarde (création ou mise à jour) de l'équipement
  public function preSave() {
  }

  // Fonction exécutée automatiquement après la sauvegarde (création ou mise à jour) de l'équipement
  public function postSave() {
  }

  // Fonction exécutée automatiquement avant la suppression de l'équipement
  public function preRemove() {
  }

  // Fonction exécutée automatiquement après la suppression de l'équipement
  public function postRemove() {
  }

  /*
  * Permet de crypter/décrypter automatiquement des champs de configuration des équipements
  * Exemple avec le champ "Mot de passe" (password)
  public function decrypt() {
    $this->setConfiguration('password', utils::decrypt($this->getConfiguration('password')));
  }
  public function encrypt() {
    $this->setConfiguration('password', utils::encrypt($this->getConfiguration('password')));
  }
  */

  /*
  * Permet de modifier l'affichage du widget (également utilisable par les commandes)
  public function toHtml($_version = 'dashboard') {}
  */

  /*
  * Permet de déclencher une action avant modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function preConfig_param3( $value ) {
    // do some checks or modify on $value
    return $value;
  }
  */

  /*
  * Permet de déclencher une action après modification d'une variable de configuration du plugin
  * Exemple avec la variable "param3"
  public static function postConfig_param3($value) {
    // no return value
  }
  */

  /*     * **********************Getteur Setteur*************************** */

  /**
   * Récupérer la configuration de l'équipement
   */
  private static function getMyConfiguration(): StdClass
  {
    log::add(__CLASS__, 'debug', 'fonction: ' . __FUNCTION__);

    $configuration = new StdClass();

    // Lecture et Analyse de la configuration

    // Nombre de jours
    log::add(__CLASS__, 'debug', ' Récupération nb jours');
    $dayNumber = config::byKey('dayNumber', __CLASS__);
    log::add(__CLASS__, 'debug', ' > Nb jours : ' . $dayNumber);

    if ($dayNumber != '') {
        if (filter_var($dayNumber, FILTER_VALIDATE_INT)) {
            $configuration->dayNumber = $dayNumber;
        } else {
            log::add(__CLASS__, 'error', ' Mauvaise valeur de dayNumber : ' . $dayNumber);
            throw new Exception('Mauvaise valeur de dayNumber : ' . $dayNumber);
        }
    } else {
        log::add(__CLASS__, 'debug', ' > Pas de dayNumber. Valeur par défaut = 7');
        $configuration->dayNumber = 7;
    }
    unset($dayNumber);

    return $configuration;
  }

  private static function cleanMessage($limitDateU) {
    log::add(__CLASS__, 'debug', 'fonction: ' . __FUNCTION__);

    // Récupere les textes présent dans le centre de messages
    $msg = "";
    $listMessage = message::all();
    foreach ($listMessage as $message){
      // Calcul de la date du message
      $date = $message->getDate();
      $dateDT = date_create_from_format('Y-m-d H:i:s', $date);
      $dateU = $dateDT->format('U');

      if ($limitDateU >= $dateU) {
        // log::add(__CLASS__, 'debug', ' date à supprimer : '.$date .', message:'.$message->getAction());
        $message->remove();
      }
      //  else {
      //   log::add(__CLASS__, 'debug', ' date à garder : '.$date .', message:'.$message->getAction());
      // }
    }
  }

  private static function cleanLog($limitDateU) {
    log::add(__CLASS__, 'debug', 'fonction: ' . __FUNCTION__);

    if (log::getConfig('log::engine') != 'StreamHandler') {
      log::add(__CLASS__, 'info', " SyslogHandler ou SyslogUdp");
      return;
    }

    // Récupere les textes présent dans le centre de messages
    $listLog = log::liste();
    $skipLog = ['cron_execution', 'http.error', 'update', ' listener_execution', 'scenario_execution',
     'logcleaner'];
    foreach ($listLog as $log) {
      if (in_array($log, $skipLog)) {
        log::add(__CLASS__, 'info', ' skip ' . $log);
        continue;
      }

      if (substr( $log, 0, strlen(self::ROOT_FILENAME)) === self::ROOT_FILENAME) {
        log::add(__CLASS__, 'info', ' skip ' . $log);
        continue;
      }

      log::add(__CLASS__, 'info', ' traitement : '.$log);

      $keepedLine = [];
      $removedLine = [];
      $lines = log::get($log, 0, 99999);
      foreach ($lines as $line) {

        // Ecriture du fichier
        log::add(__CLASS__, 'info', ' > nettoyage');

        // Extract date from string
        $extract_date_pattern = '/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/';
        preg_match($extract_date_pattern, $line, $matches);
        // Calcul de la date du message
        $date = $matches[0];
        if ($date != '') {
          $dateDT = date_create_from_format('Y-m-d H:i:s', $date);
          $dateU = $dateDT->format('U');
        }

        if ($date != '' && $dateU != '' && $limitDateU >= $dateU) {
          array_push($removedLine, $line);
        } else {
          array_push($keepedLine, $line);
        }
      }
      
      if (count($keepedLine) > 0) {
        // Ecriture du fichier
        log::add(__CLASS__, 'info', ' > save');

        // Log non vide
        $keepedLine = array_reverse($keepedLine);
        $content = implode(PHP_EOL, $keepedLine);
        log::clear($log);
        file_put_contents($log, $content);
      } else {
        // Log vide
        $deleteEmpty = config::byKey('deleteEmpty', __CLASS__, '');
        log::add(__CLASS__, 'debug', ' > deleteEmpty : ' . $deleteEmpty);
        if ($deleteEmpty == '1') {
          log::add(__CLASS__, 'info', ' > delete empty');

          $filename = log::getPathToLog('') . DIRECTORY_SEPARATOR . $log;
          unlink($filename);
        }
      }

      // Full backup
      $fullBackup = config::byKey('fullBackup', __CLASS__, '');
      log::add(__CLASS__, 'debug', ' > fullBackup : ' . $fullBackup);
      if ($fullBackup == '1') {
        log::add(__CLASS__, 'info', ' > full backup');

        $filename = log::getPathToLog('') . DIRECTORY_SEPARATOR . self::ROOT_FILENAME . $log . '_full.bak';
        $lines = array_reverse($lines);
        $content = implode(PHP_EOL, $lines);
        file_put_contents($filename, $content);
      }

      // KeepedLine
      // $filename = log::getPathToLog('') . DIRECTORY_SEPARATOR . $log . '_keeped.txt';
      // $keepedLine = array_reverse($keepedLine);
      // $content = implode(PHP_EOL, $keepedLine);
      // file_put_contents($filename, $content);
      
      // Full backup
      $differentialBackup = config::byKey('differentialBackup', __CLASS__, '');
      log::add(__CLASS__, 'debug', ' > differentialBackup : ' . $differentialBackup);
      if ($differentialBackup == '1' &&  count($removedLine) > 0) {
        log::add(__CLASS__, 'info', ' > differential backup');

        // removedLine
        $filename = log::getPathToLog('') . DIRECTORY_SEPARATOR . self::ROOT_FILENAME . $log . '_removed.bak';
        $removedLine = array_reverse($removedLine);
        $content = implode(PHP_EOL, $removedLine);
        file_put_contents($filename, $content);
      }
    }
  }

  // Exécution d'une commande
  public static function execute($_options = array()) {
    log::add(__CLASS__, 'info', " **** execute ****");

    $configuration = logcleaner::getMyConfiguration();

    // calcul de la date limite
    $limitDate = new DateTime('today midnight');
    $limitDate->modify("-$configuration->dayNumber day");
    log::add(__CLASS__, 'info', 'Date calculée : ' . $limitDate->format("Y-m-d H:i:s"));
    $limitDateU= $limitDate->format('U');

    logcleaner::cleanMessage($limitDateU);

    logcleaner::cleanLog($limitDateU);
  }
}

class logcleanerCmd extends cmd {
  /*     * *************************Attributs****************************** */

  /*
  public static $_widgetPossibility = array();
  */

  /*     * ***********************Methode static*************************** */


  /*     * *********************Methode d'instance************************* */

  /*
  * Permet d'empêcher la suppression des commandes même si elles ne sont pas dans la nouvelle configuration de l'équipement envoyé en JS
  public function dontRemoveCmd() {
    return true;
  }
  */

  // Exécution d'une commande
  public function execute($_options = array()) {
    
  }

  /*     * **********************Getteur Setteur*************************** */

}
