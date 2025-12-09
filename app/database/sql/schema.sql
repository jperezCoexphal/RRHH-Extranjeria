

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';


CREATE SCHEMA IF NOT EXISTS `mydb` DEFAULT CHARACTER SET utf8 ;
USE `mydb` ;

-- -----------------------------------------------------
-- Table `mydb`.`foreigners`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`foreigners` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `surname_1` VARCHAR(150) NOT NULL,
  `passport` VARCHAR(50) NOT NULL,
  `niss` VARCHAR(12) NOT NULL,
  `nie` VARCHAR(9) NOT NULL,
  `gender` ENUM('H', 'M', 'X') NOT NULL,
  `birthdate` DATE NOT NULL,
  `nationality` VARCHAR(75) NOT NULL,
  `marital_status` ENUM('S', 'C', 'V', 'D', 'Sp') NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `passport_num_UNIQUE` (`passport` ASC) VISIBLE,
  UNIQUE INDEX `niss_UNIQUE` (`niss` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`countries`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`countries` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `iso_code_2` CHAR(2) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`provinces`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`provinces` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `country_id` INT NULL,
  `name` VARCHAR(45) NOT NULL,
  `code` VARCHAR(5) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_provinces_to_countries_idx` (`country_id` ASC) VISIBLE,
  CONSTRAINT `fk_provinces_to_countries`
    FOREIGN KEY (`country_id`)
    REFERENCES `mydb`.`countries` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`municipalities`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`municipalities` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `province_id` INT NULL,
  `name` VARCHAR(100) NOT NULL,
  `code` VARCHAR(5) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `code_UNIQUE` (`code` ASC) VISIBLE,
  INDEX `fk_municipalities_to_provinces_idx` (`province_id` ASC) VISIBLE,
  CONSTRAINT `fk_municipalities_to_provinces`
    FOREIGN KEY (`province_id`)
    REFERENCES `mydb`.`provinces` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`addresses`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`addresses` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `municipality_id` INT NULL,
  `postal_code` VARCHAR(15) NOT NULL,
  `street_address` VARCHAR(255) NOT NULL,
  `num` INT NOT NULL,
  `floor_apt_unit` VARCHAR(25) NULL,
  `address_type` VARCHAR(45) NULL,
  `addressable_id` INT NULL,
  `addressable_type` VARCHAR(255) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_addresses_to_municipalities_idx` (`municipality_id` ASC) VISIBLE,
  CONSTRAINT `fk_addresses_to_municipalities`
    FOREIGN KEY (`municipality_id`)
    REFERENCES `mydb`.`municipalities` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`users` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(150) NOT NULL,
  `surname` VARCHAR(150) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `profile_avatar_url` VARCHAR(255) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`permissions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`permissions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `action` VARCHAR(255) NOT NULL,
  `description` TEXT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`roles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(20) NOT NULL,
  `description` TEXT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`role_permissions`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`role_permissions` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `role_id` INT NOT NULL,
  `permission_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_role_permissions_to_permissions_idx` (`permission_id` ASC) VISIBLE,
  INDEX `fk_role_permissions_to_roles_idx` (`role_id` ASC) VISIBLE,
  CONSTRAINT `fk_role_permissions_to_permissions`
    FOREIGN KEY (`permission_id`)
    REFERENCES `mydb`.`permissions` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_role_permissions_to_roles`
    FOREIGN KEY (`role_id`)
    REFERENCES `mydb`.`roles` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`user_roles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`user_roles` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT NOT NULL,
  `role_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_user_roles_to_roles_idx` (`role_id` ASC) VISIBLE,
  INDEX `fk_user_roles_to_users_idx` (`user_id` ASC) VISIBLE,
  CONSTRAINT `fk_user_roles_to_roles`
    FOREIGN KEY (`role_id`)
    REFERENCES `mydb`.`roles` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_roles_to_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `mydb`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`legal_representatives`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`legal_representatives` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `legal_name` VARCHAR(255) NOT NULL,
  `nie_dni_pass` VARCHAR(50) NOT NULL,
  `phone_number` VARCHAR(20) NULL,
  `legal_guardian_name` VARCHAR(255) NULL,
  `legal_guardian_id` VARCHAR(20) NULL,
  `guardianship_title` VARCHAR(75) NULL,
  `user_id` INT NOT NULL,
  `role_id` INT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_legal_representatives_to_roles_idx` (`role_id` ASC) VISIBLE,
  INDEX `fk_legal_representatives_to_users_idx` (`user_id` ASC) VISIBLE,
  CONSTRAINT `fk_legal_representatives_to_roles`
    FOREIGN KEY (`role_id`)
    REFERENCES `mydb`.`roles` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_legal_representatives_to_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `mydb`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`employers`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`employers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `legal_type` ENUM('JURIDICA', 'FISICA') NOT NULL,
  `nif` VARCHAR(9) NOT NULL,
  `legal_name` VARCHAR(255) NOT NULL,
  `activity_main` VARCHAR(255) NULL,
  `cnae` VARCHAR(10) NULL,
  `phone_number` VARCHAR(20) NULL,
  `email` VARCHAR(255) NULL,
  `is_partner` TINYINT NOT NULL DEFAULT 1,
  `is_sent_to_billing` TINYINT GENERATED ALWAYS AS (0) VIRTUAL,
  `creation_date` DATE NOT NULL DEFAULT CURDATE(),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `nif_UNIQUE` (`nif` ASC) VISIBLE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`companies`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`companies` (
  `employer_id` INT NOT NULL,
  `rep_nie_dni_pass` VARCHAR(20) NULL,
  `rep_title` VARCHAR(100) NULL,
  `cno_sepe_2011` VARCHAR(20) NULL,
  PRIMARY KEY (`employer_id`),
  CONSTRAINT `fk_companies_to_employers`
    FOREIGN KEY (`employer_id`)
    REFERENCES `mydb`.`employers` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`freelancers`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`freelancers` (
  `employer_id` INT NOT NULL AUTO_INCREMENT,
  `birthdate` DATE NOT NULL,
  `niss` VARCHAR(12) NOT NULL,
  PRIMARY KEY (`employer_id`),
  CONSTRAINT `fk_freelancers_to_employers`
    FOREIGN KEY (`employer_id`)
    REFERENCES `mydb`.`employers` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`application_types`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`application_types` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(10) NOT NULL,
  `name` VARCHAR(125) NOT NULL,
  `description` TEXT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`file_statuses`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`file_statuses` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `status_name` VARCHAR(50) NOT NULL,
  `is_final` TINYINT NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`resolution_statuses`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`resolution_statuses` (
  `id` INT NOT NULL,
  `name` VARCHAR(45) NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`inmigration_files`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`inmigration_files` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `file_code` VARCHAR(45) NOT NULL,
  `file_title` VARCHAR(100) NOT NULL,
  `legal_representative_id` INT NOT NULL,
  `employer_id` INT NOT NULL,
  `foreinger_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  `main_company_id` INT NULL,
  `creation_date` DATE NOT NULL DEFAULT CURDATE(),
  `application_type_id` INT NOT NULL,
  `file_status_id` INT NULL,
  `resolution_status_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `file_code_UNIQUE` (`file_code` ASC) VISIBLE,
  UNIQUE INDEX `file_title_UNIQUE` (`file_title` ASC) VISIBLE,
  INDEX `fk_inmigration_files_to_legal_representatives_idx` (`legal_representative_id` ASC) VISIBLE,
  INDEX `fk_inmigration_files_to_employers_idx` (`employer_id` ASC) VISIBLE,
  INDEX `fk_inmigration_files_to_foreigners_idx` (`foreinger_id` ASC) VISIBLE,
  INDEX `fk_inmigration_files_to_users_idx` (`user_id` ASC) VISIBLE,
  INDEX `fk_inmigration_files_to_employers_idx1` (`main_company_id` ASC) VISIBLE,
  INDEX `fk_inmigration_files_to_application_types_idx` (`application_type_id` ASC) VISIBLE,
  INDEX `fk_inmigration_files_to_fk_inmigration_files_to_file_status_idx` (`file_status_id` ASC) VISIBLE,
  INDEX `fk_inmigration_files_resolution_status1_idx` (`resolution_status_id` ASC) VISIBLE,
  CONSTRAINT `fk_inmigration_files_to_legal_representatives`
    FOREIGN KEY (`legal_representative_id`)
    REFERENCES `mydb`.`legal_representatives` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inmigration_files_to_employers`
    FOREIGN KEY (`employer_id`)
    REFERENCES `mydb`.`employers` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inmigration_files_to_foreigners`
    FOREIGN KEY (`foreinger_id`)
    REFERENCES `mydb`.`foreigners` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inmigration_files_to_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `mydb`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inmigration_files_to_employers`
    FOREIGN KEY (`main_company_id`)
    REFERENCES `mydb`.`employers` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inmigration_files_to_application_types`
    FOREIGN KEY (`application_type_id`)
    REFERENCES `mydb`.`application_types` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inmigration_files_to_fk_inmigration_files_to_file_statuses`
    FOREIGN KEY (`file_status_id`)
    REFERENCES `mydb`.`file_statuses` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inmigration_files_resolution_status1`
    FOREIGN KEY (`resolution_status_id`)
    REFERENCES `mydb`.`resolution_statuses` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



-- -----------------------------------------------------
-- Table `mydb`.`file_status_history`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`file_status_history` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `inmigration_file_id` INT NOT NULL,
  `file_status_id` INT NOT NULL,
  `change_date` DATE NOT NULL DEFAULT CURDATE(),
  `user_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `file_status_history_to_file_statuses_idx` (`file_status_id` ASC) VISIBLE,
  INDEX `file_status_history_to_users_idx` (`user_id` ASC) VISIBLE,
  INDEX `file_status_history_to_inmigration_files_idx` (`inmigration_file_id` ASC) VISIBLE,
  CONSTRAINT `file_status_history_to_file_statuses`
    FOREIGN KEY (`file_status_id`)
    REFERENCES `mydb`.`file_statuses` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `file_status_history_to_users`
    FOREIGN KEY (`user_id`)
    REFERENCES `mydb`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `file_status_history_to_inmigration_files`
    FOREIGN KEY (`inmigration_file_id`)
    REFERENCES `mydb`.`inmigration_files` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`check_categories`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`check_categories` (
  `id` INT NOT NULL,
  `name` VARCHAR(80) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`check_types`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`check_types` (
  `id` INT NOT NULL,
  `name` VARCHAR(80) NOT NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`checklist`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`checklist` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` TEXT NOT NULL,
  `employer_legal_type` ENUM('JURIDICA', 'FISICA') NULL,
  `check_categories_id` INT NOT NULL,
  `check_types_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_checklist_check_categories1_idx` (`check_categories_id` ASC) VISIBLE,
  INDEX `fk_checklist_check_types1_idx` (`check_types_id` ASC) VISIBLE,
  CONSTRAINT `fk_checklist_check_categories1`
    FOREIGN KEY (`check_categories_id`)
    REFERENCES `mydb`.`check_categories` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_checklist_check_types1`
    FOREIGN KEY (`check_types_id`)
    REFERENCES `mydb`.`check_types` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`foreigners_relatives`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`foreigners_relatives` (
  `id` INT NOT NULL,
  `file id` VARCHAR(45) NULL,
  `foreigner_id` INT NULL,
  `relative_id` INT NULL,
  `relationship_type` VARCHAR(45) NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_foreigners_relatives_to_foreigners_idx` (`foreigner_id` ASC) VISIBLE,
  INDEX `fk_foreigners_relatives_relatives_idx` (`relative_id` ASC) VISIBLE,
  CONSTRAINT `fk_foreigners_relatives_foreigner`
    FOREIGN KEY (`foreigner_id`)
    REFERENCES `mydb`.`foreigners` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_foreigners_relatives_relatives`
    FOREIGN KEY (`relative_id`)
    REFERENCES `mydb`.`foreigners` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`foreigners_extra_data`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`foreigners_extra_data` (
  `foreigner_id` INT NOT NULL,
  `surname_2` VARCHAR(150) NULL,
  `father_name` VARCHAR(150) NULL,
  `mother_name` VARCHAR(150) NULL,
  `legal_guardian_name` VARCHAR(255) NULL,
  `legal_guardian_id` VARCHAR(20) NULL,
  `guardianship_title` VARCHAR(100) NULL,
  `phone_number` VARCHAR(20) NULL,
  `email` VARCHAR(255) NULL,
  PRIMARY KEY (`foreigner_id`),
  CONSTRAINT `fk_foreigner_extra_data_to_foreigner`
    FOREIGN KEY (`foreigner_id`)
    REFERENCES `mydb`.`foreigners` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



-- -----------------------------------------------------
-- Table `mydb`.`checklist_workflows`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`checklist_workflows` (
  `id` INT NOT NULL,
  `application_types_id` INT NOT NULL,
  `file_statuses_id` INT NOT NULL,
  `checklist_id` INT NOT NULL,
  `is_required` TINYINT NULL,
  `limit_days` INT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_checklist_workflow_application_types1_idx` (`application_types_id` ASC) VISIBLE,
  INDEX `fk_checklist_workflow_file_statuses1_idx` (`file_statuses_id` ASC) VISIBLE,
  INDEX `fk_checklist_workflow_checklist1_idx` (`checklist_id` ASC) VISIBLE,
  CONSTRAINT `fk_checklist_workflow_application_types1`
    FOREIGN KEY (`application_types_id`)
    REFERENCES `mydb`.`application_types` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_checklist_workflow_file_statuses1`
    FOREIGN KEY (`file_statuses_id`)
    REFERENCES `mydb`.`file_statuses` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_checklist_workflow_checklist1`
    FOREIGN KEY (`checklist_id`)
    REFERENCES `mydb`.`checklist` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`inmigration_file_checklists`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`inmigration_file_checklists` (
  `id` INT NOT NULL,
  `inmigration_file_id` INT NOT NULL,
  `is_completed` TINYINT NOT NULL DEFAULT 0,
  `notification_date` DATE NULL,
  `completed_date` DATE NULL,
  `comments` TEXT NULL,
  `user_id` INT NULL,
  `checklist_workflow_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_checklist_has_inmigration_files_inmigration_files1_idx` (`inmigration_file_id` ASC) VISIBLE,
  INDEX `fk_inmigration_file_checklist_users1_idx` (`user_id` ASC) VISIBLE,
  INDEX `fk_inmigration_file_checklist_checklist_workflow1_idx` (`checklist_workflow_id` ASC) VISIBLE,
  CONSTRAINT `fk_checklist_has_inmigration_files_inmigration_files1`
    FOREIGN KEY (`inmigration_file_id`)
    REFERENCES `mydb`.`inmigration_files` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inmigration_file_checklist_users1`
    FOREIGN KEY (`user_id`)
    REFERENCES `mydb`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inmigration_file_checklist_checklist_workflow1`
    FOREIGN KEY (`checklist_workflow_id`)
    REFERENCES `mydb`.`checklist_workflows` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`document_templates`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`document_templates` (
  `id` INT NOT NULL,
  `template_name` VARCHAR(150) NULL,
  `template_path` VARCHAR(255) NULL,
  `template_maping` JSON NULL,
  PRIMARY KEY (`id`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`application_template_config`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`application_template_config` (
  `id` INT NOT NULL,
  `application_types_id` INT NOT NULL,
  `document_templates_id` INT NOT NULL,
  `is_mandatory_to_generate` TINYINT NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`),
  INDEX `fk_application_types_has_document_templates_document_templa_idx` (`document_templates_id` ASC) VISIBLE,
  INDEX `fk_application_types_has_document_templates_application_typ_idx` (`application_types_id` ASC) VISIBLE,
  CONSTRAINT `fk_application_types_has_document_templates_application_types1`
    FOREIGN KEY (`application_types_id`)
    REFERENCES `mydb`.`application_types` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_application_types_has_document_templates_document_templates1`
    FOREIGN KEY (`document_templates_id`)
    REFERENCES `mydb`.`document_templates` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `mydb`.`contract_details`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`contract_details` (
  `inmigrant_file_id` INT NOT NULL,
  `job_title` VARCHAR(100) NULL,
  `contract_salary` DECIMAL(10,2) NULL,
  `contract_code` VARCHAR(20) NULL,
  `work_hours` DECIMAL(4,2) NULL,
  `cno_sepe_2011_code` VARCHAR(20) NULL,
  PRIMARY KEY (`inmigrant_file_id`),
  CONSTRAINT `fk_contract_details_to_inmigrantion_file`
    FOREIGN KEY (`inmigrant_file_id`)
    REFERENCES `mydb`.`inmigration_files` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
