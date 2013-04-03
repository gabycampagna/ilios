<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 * Sets up the tables needed for mapping out the curriculum inventory according to the AAMC standard.
 */
class Migration_Setup_curriculum_inventory_tables extends CI_Migration
{
    /**
     * @see CI_Migration::up()
     */
    public function up ()
    {
        $this->db->trans_start();
        $sql =<<<EOL
CREATE TABLE `curriculum_inventory_institution` (
    `school_id` INT(10) UNSIGNED NOT NULL,
    `name` VARCHAR(100) NOT NULL,
    `aamc_id` VARCHAR(10) NOT NULL,
    `address_street` VARCHAR(100) NOT NULL,
    `address_city` VARCHAR(100) NOT NULL,
    `address_state_or_province` VARCHAR(50) NOT NULL,
    `address_zipcode` VARCHAR(10) NOT NULL,
    `address_country_code` CHAR(2) NOT NULL,
    PRIMARY KEY (`school_id`),
    CONSTRAINT `fkey_curriculum_inventory_institution_school_id`
       FOREIGN KEY (`school_id`) REFERENCES `school` (`school_id`)
       ON UPDATE CASCADE ON DELETE CASCADE
)
DEFAULT CHARSET='utf8'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
EOL;
        $this->db->query($sql);
        $sql =<<<EOL
CREATE TABLE `curriculum_inventory_program` (
    `program_year_id` INT(10) UNSIGNED NOT NULL,
    `aamc_id` VARCHAR(10) NULL,
    `name` VARCHAR(200) NULL DEFAULT NULL,
    `education_program_context_id` INT UNSIGNED NULL,
    `profession_id` INT UNSIGNED NULL,
    `specialty_id` INT UNSIGNED NULL,
    `start_date` DATE NULL DEFAULT NULL,
    `end_date` DATE NULL DEFAULT NULL,
    PRIMARY KEY (`program_year_id`),
    CONSTRAINT `fkey_curriculum_inventory_program_program_year_id`
        FOREIGN KEY (`program_year_id`) REFERENCES `program_year` (`program_year_id`)
        ON UPDATE CASCADE ON DELETE CASCADE
)
DEFAULT CHARSET='utf8'
COLLATE='utf8_general_ci'
ENGINE=InnoDB
EOL;
        $this->db->query($sql);
        $this->db->trans_complete();
    }

    /**
     * @see CI_Migration::down()
     */
    public function down ()
    {
        $this->db->trans_start();
        $this->db->query("DROP TABLE `curriculum_inventory_institution`");
        $this->db->query("DROP TABLE `curriculum_inventory_program`");
        $this->db->trans_complete();
    }
}