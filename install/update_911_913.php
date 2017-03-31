<?php
/*
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2015-2016 Teclib'.

 http://glpi-project.org

 based on GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2014 by the INDEPNET Development Team.

 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 */

/** @file
* @brief
*/

/**
 * Update from 9.1.1 to 9.1.3
 *
 * @return bool for success (will die for most error)
**/
function update911to913() {
   global $DB, $migration, $CFG_GLPI;

   $current_config   = Config::getConfigurationValues('core');
   $updateresult     = true;
   $ADDTODISPLAYPREF = array();

   //TRANS: %s is the number of new version
   $migration->displayTitle(sprintf(__('Update to %s'), '9.1.3'));
   $migration->setVersion('9.1.3');

   $backup_tables = false;
   // table already exist but deleted during the migration
   // not table created during the migration
   $newtables     = array();

   foreach ($newtables as $new_table) {
      // rename new tables if exists ?
      if (TableExists($new_table)) {
         $migration->dropTable("backup_$new_table");
         $migration->displayWarning("$new_table table already exists. ".
                                    "A backup have been done to backup_$new_table.");
         $backup_tables = true;
         $query         = $migration->renameTable("$new_table", "backup_$new_table");
      }
   }
   if ($backup_tables) {
      $migration->displayWarning("You can delete backup tables if you have no need of them.",
                                 true);
   }

   //Fix duplicated search options
   if (countElementsInTable("glpi_displaypreferences", "`itemtype` = 'IPNetwork' AND `num` = '17'") == 0) {
      $query = "UPDATE `glpi_displaypreferences`
                  SET num = '17' WHERE itemtype = 'IPNetwork' AND num = '13'";
      $DB->queryOrDie($query, "9.1.3 Fix duplicate IPNetwork Gateway search option");
   }
   if (countElementsInTable("glpi_displaypreferences", "`itemtype` = 'IPNetwork' AND `num` = '18'") == 0) {
      $query = "UPDATE `glpi_displaypreferences`
                  SET num = '18' WHERE itemtype = 'IPNetwork' AND num = '14'";
      $DB->queryOrDie($query, "9.1.3 Fix duplicate IPNetwork addressable network search option");
   }

   // ************ Keep it at the end **************
   $migration->executeMigration();

   return $updateresult;
}
