-- -----------------------------------------------------
-- Table `mydb`.`employers`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`employers` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `legal_form` ENUM('AIE', 'CB', 'ERL', 'EI', 'SA', 'SAL', 'SAT', 'SCP', 'SC', 'SCA', 'COOP', 'SCS', 'ECR', 'SCTA', 'SGR', 'SRLL', 'SP', 'SL') NOT NULL,
  `comercial_name` VARCHAR(100) NULL,
  `fiscal_name` VARCHAR(100) NOT NULL,
  `nif` VARCHAR(9) NOT NULL,
  `ccc` VARCHAR(11) NOT NULL,
  `cnae` VARCHAR(4) NOT NULL,
  `email` VARCHAR(255) NULL,
  `phone` VARCHAR(30) NULL,
  `is_associated` TINYINT NOT NULL,
  `created_at` TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `fiscal_name_UNIQUE` (`fiscal_name` ASC) VISIBLE,
  UNIQUE INDEX `nif_UNIQUE` (`nif` ASC) VISIBLE,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) VISIBLE);


-- -----------------------------------------------------
-- Table `mydb`.`freelancers`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`freelancers` (
  `employer_id` INT NOT NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `birthdate` DATE NOT NULL,
  `niss` VARCHAR(12) NOT NULL,
  PRIMARY KEY (`employer_id`),
  UNIQUE INDEX `niss_UNIQUE` (`niss` ASC) VISIBLE,
  CONSTRAINT `fk_freelancers_to_employers`
    FOREIGN KEY (`employer_id`)
    REFERENCES `mydb`.`employers` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `mydb`.`companies`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`companies` (
  `employer_id` INT NOT NULL,
  `representative_name` VARCHAR(150) NOT NULL,
  `representative_title` VARCHAR(100) NOT NULL,
  `representative_identity_number` VARCHAR(9) NOT NULL,
  PRIMARY KEY (`employer_id`),
  UNIQUE INDEX `representative_dni_UNIQUE` (`representative_identity_number` ASC) VISIBLE,
  CONSTRAINT `fk_companies_to_employers`
    FOREIGN KEY (`employer_id`)
    REFERENCES `mydb`.`employers` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `mydb`.`countries`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`countries` (
  `id` INT NOT NULL,
  `country_name` VARCHAR(100) NOT NULL,
  `iso_code_2` CHAR(2) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `country_name_UNIQUE` (`country_name` ASC) VISIBLE,
  UNIQUE INDEX `iso_code_2_UNIQUE` (`iso_code_2` ASC) VISIBLE);


-- -----------------------------------------------------
-- Table `mydb`.`foreigners`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`foreigners` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `passport` VARCHAR(44) NOT NULL,
  `nie` VARCHAR(9) NOT NULL,
  `niss` VARCHAR(12) NULL,
  `gender` ENUM('H', 'M', 'X') NOT NULL,
  `birthdate` DATE NOT NULL,
  `marital_status` ENUM('Sol', 'Cas', 'Viu', 'Sep', 'Div') NOT NULL,
  `nationality_id` INT NOT NULL,
  `birth_country_id` INT NOT NULL,
  `birthplace_name` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `nie_UNIQUE` (`nie` ASC) VISIBLE,
  UNIQUE INDEX `passport_UNIQUE` (`passport` ASC) VISIBLE,
  UNIQUE INDEX `niss_UNIQUE` (`niss` ASC) VISIBLE,
  INDEX `fk_foreigners_to_countries_nationality_idx` (`nationality_id` ASC) VISIBLE,
  INDEX `fk_foreigners_to_countries_birth_idx` (`birth_country_id` ASC) VISIBLE,
  CONSTRAINT `fk_foreigners_to_countries_nationality`
    FOREIGN KEY (`nationality_id`)
    REFERENCES `mydb`.`countries` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_foreigners_to_countries_birth`
    FOREIGN KEY (`birth_country_id`)
    REFERENCES `mydb`.`countries` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `mydb`.`foreigners_extra_data`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`foreigners_extra_data` (
  `foreigner_id` INT NOT NULL,
  `father_name` VARCHAR(150) NULL,
  `mother_name` VARCHAR(150) NULL,
  `legal_guardian_name` VARCHAR(150) NULL,
  `legal_guardian_identity_number` VARCHAR(44) NULL,
  `guardianship_title` VARCHAR(50) NULL,
  `phone` VARCHAR(30) NULL,
  `email` VARCHAR(255) NULL,
  PRIMARY KEY (`foreigner_id`),
  CONSTRAINT `fk_foreigner_extra_data_to_foreigner`
    FOREIGN KEY (`foreigner_id`)
    REFERENCES `mydb`.`foreigners` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `mydb`.`foreigner_relationships`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`foreigner_relationships` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `relation_type` ENUM('Spouse', 'Partner', 'Minor', 'Adult', 'Ascendants', 'Extended') NOT NULL,
  `foreigner_id` INT NOT NULL,
  `related_foreigner_id` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_to_foreigners_as_principal_idx` (`foreigner_id` ASC) VISIBLE,
  INDEX `fk_to_foreigners_as_relative_idx` (`related_foreigner_id` ASC) VISIBLE,
  CONSTRAINT `fk_to_foreigners_as_principal`
    FOREIGN KEY (`foreigner_id`)
    REFERENCES `mydb`.`foreigners` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_to_foreigners_as_relative`
    FOREIGN KEY (`related_foreigner_id`)
    REFERENCES `mydb`.`foreigners` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `mydb`.`provinces`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`provinces` (
  `id` INT NOT NULL,
  `province_name` VARCHAR(50) NOT NULL,
  `provice_code` CHAR(2) NOT NULL,
  `country_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_provinces_to_countries_idx` (`country_id` ASC) VISIBLE,
  UNIQUE INDEX `province_name_UNIQUE` (`province_name` ASC) VISIBLE,
  UNIQUE INDEX `ine_code_UNIQUE` (`provice_code` ASC) VISIBLE,
  CONSTRAINT `fk_provinces_to_countries`
    FOREIGN KEY (`country_id`)
    REFERENCES `mydb`.`countries` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `mydb`.`municipalities`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`municipalities` (
  `id` INT NOT NULL,
  `municipality_name` VARCHAR(100) NOT NULL,
  `municipality_code` CHAR(3) NOT NULL,
  `province_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_municipalities_to_provinces_idx` (`province_id` ASC) VISIBLE,
  UNIQUE INDEX `municipality_name_UNIQUE` (`municipality_name` ASC) VISIBLE,
  UNIQUE INDEX `municipality_code_UNIQUE` (`municipality_code` ASC) VISIBLE,
  CONSTRAINT `fk_municipalities_to_provinces`
    FOREIGN KEY (`province_id`)
    REFERENCES `mydb`.`provinces` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `mydb`.`addresses`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`addresses` (
  `id` INT NOT NULL,
  `addressable_id` INT NOT NULL,
  `addressable_type` VARCHAR(40) NOT NULL,
  `postal_code` CHAR(5) NOT NULL,
  `street_name` VARCHAR(150) NOT NULL,
  `number` VARCHAR(3) NULL,
  `floor_door` VARCHAR(20) NULL,
  `country_id` INT NOT NULL,
  `province_id` INT NOT NULL,
  `municipality_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_addresses_to_countries_idx` (`country_id` ASC) VISIBLE,
  INDEX `fk_addresses_to_provinces_idx` (`province_id` ASC) VISIBLE,
  INDEX `fk_addresses_to_municipalities_idx` (`municipality_id` ASC) VISIBLE,
  INDEX `addressable_index` (`addressable_id` ASC, `addressable_type` ASC) VISIBLE,
  CONSTRAINT `fk_addresses_to_countries`
    FOREIGN KEY (`country_id`)
    REFERENCES `mydb`.`countries` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_addresses_to_provinces`
    FOREIGN KEY (`province_id`)
    REFERENCES `mydb`.`provinces` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_addresses_to_municipalities`
    FOREIGN KEY (`municipality_id`)
    REFERENCES `mydb`.`municipalities` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `mydb`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`users` (
  `id` INT NOT NULL,
  `first_name` VARCHAR(50) NOT NULL,
  `last_name` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `created_at` TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`));


-- -----------------------------------------------------
-- Table `mydb`.`inmigration_files`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`inmigration_files` (
  `id` INT NOT NULL,
  `campaign` CHAR(9) NOT NULL,
  `file_code` VARCHAR(12) NOT NULL,
  `file_title` VARCHAR(165) NOT NULL,
  `application_type` ENUM('EX-00', 'EX-01', 'EX-02', 'EX-03', 'EX-04', 'EX-05', 'EX-06', 'EX-07', 'EX-08', 'EX-09', 'EX-10', 'EX-11', 'EX-12', 'EX-13', 'EX-14', 'EX-15', 'EX-16', 'EX-17', 'EX-18', 'EX-19', 'EX-20', 'EX-21', 'EX-22', 'EX-23', 'EX-24', 'EX-25', 'EX-26', 'EX-27', 'EX-28', 'EX-29', 'EX-30') NOT NULL,
  `status` ENUM('borrador', 'pendiente_revision', 'listo', 'presentado', 'requerido', 'favorable', 'denegado', 'archivado') NOT NULL,
  `job_title` VARCHAR(50) NOT NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NULL,
  `salary` DECIMAL NULL,
  `working_day_type` ENUM('completa', 'parcial', 'discontinuo') NULL,
  `working_hours` FLOAT NULL,
  `probation_period` INT NULL,
  `editor_id` INT NOT NULL,
  `employer_id` INT NOT NULL,
  `foreigner_id` INT NOT NULL,
  `created_at` TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_inmigration_files_to_employers_idx` (`employer_id` ASC) VISIBLE,
  INDEX `fk_inmigration_files_to_foreigners_idx` (`foreigner_id` ASC) VISIBLE,
  INDEX `fk_inmigration_files_to_users_as_editor_idx` (`editor_id` ASC) VISIBLE,
  CONSTRAINT `fk_inmigration_files_to_employers`
    FOREIGN KEY (`employer_id`)
    REFERENCES `mydb`.`employers` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inmigration_files_to_foreigners`
    FOREIGN KEY (`foreigner_id`)
    REFERENCES `mydb`.`foreigners` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_inmigration_files_to_users_as_editor`
    FOREIGN KEY (`editor_id`)
    REFERENCES `mydb`.`users` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


-- -----------------------------------------------------
-- Table `mydb`.`requirement_templates`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`requirement_templates` (
  `id` INT NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `description` TEXT NULL,
  `target_entity` ENUM('WORKER', 'EMPLOYER', 'REPRESENTATIVE', 'GENERAL') NULL,
  `application_type` ENUM('EX-00', 'EX-01', 'EX-02', 'EX-03', 'EX-04', 'EX-05', 'EX-06', 'EX-07', 'EX-08', 'EX-09', 'EX-10', 'EX-11', 'EX-12', 'EX-13', 'EX-14', 'EX-15', 'EX-16', 'EX-17', 'EX-18', 'EX-19', 'EX-20', 'EX-21', 'EX-22', 'EX-23', 'EX-24', 'EX-25', 'EX-26', 'EX-27', 'EX-28', 'EX-29', 'EX-30') NULL,
  `trigger_status` ENUM('borrador', 'pendiente_revision', 'listo', 'presentado', 'requerido', 'favorable', 'denegado', 'archivado') NULL,
  `days_to_expire` INT NULL,
  `is_mandatory` TINYINT NOT NULL,
  `created_at` TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`));



-- -----------------------------------------------------
-- Table `mydb`.`file_requirements`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `mydb`.`file_requirements` (
  `id` INT NOT NULL,
  `name` VARCHAR(120) NOT NULL,
  `description` TEXT NULL,
  `target_entity` ENUM('WORKER', 'EMPLOYER', 'REPRESENTATIVE', 'GENERAL') NULL,
  `observation` TEXT NULL,
  `due_date` DATE NULL,
  `is_completed` TINYINT NOT NULL,
  `completed_at` TIMESTAMP NULL,
  `notified_at` TIMESTAMP NULL,
  `inmigration_file_id` INT NOT NULL,
  `requeriment_template_id` INT NULL,
  `created_at` TIMESTAMP NOT NULL,
  `updated_at` TIMESTAMP NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_file_requeriments_to_inmigration_files_idx` (`inmigration_file_id` ASC) VISIBLE,
  INDEX `fk_file_requeriments_to_requeriment_templates_idx` (`requeriment_template_id` ASC) VISIBLE,
  CONSTRAINT `fk_file_requeriments_to_inmigration_files`
    FOREIGN KEY (`inmigration_file_id`)
    REFERENCES `mydb`.`inmigration_files` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_file_requeriments_to_requeriment_templates`
    FOREIGN KEY (`requeriment_template_id`)
    REFERENCES `mydb`.`requirement_templates` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);

