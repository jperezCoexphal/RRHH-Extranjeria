
-- CLIENTS \ EMPLOYERS

    -- -----------------------------------------------------
    -- Table `mydb`.`employers`
    -- -----------------------------------------------------

    CREATE TABLE IF NOT EXISTS `employers` 
    (
        `id` INT NOT NULL AUTO_INCREMENT,
        `legal_type` ENUM('COMPANY', 'FREELANCER') NOT NULL,
        `nif` VARCHAR(9) NOT NULL,
        `legal_name` VARCHAR(255) NOT NULL,
        `activity_main` VARCHAR(255) NOT NULL,
        `cnae` VARCHAR(5) NULL,
        `phone_number` VARCHAR(255) NULL,
        `email` VARCHAR(255) NULL,
        `is_partner` TINYINT NOT NULL DEFAULT 1,
        `created_at` TIMESTAMP,
        `updated_at` TIMESTAMP,
        PRIMARY KEY (`id`)
    );

    -- -----------------------------------------------------
    -- Table `mydb`.`freelancers`
    -- -----------------------------------------------------

    CREATE TABLE IF NOT EXISTS `freelancers` 
    (
        `employer_id` INT NOT NULL,
        `birthdate` DATE NOT NULL,
        `niss` VARCHAR(12) NOT NULL,
        PRIMARY KEY (`employer_id`),
        UNIQUE INDEX `niss_UNIQUE` (`niss` ASC),
        CONSTRAINT `fk_freelancers_to_employers`
            FOREIGN KEY (`employer_id`)
            REFERENCES `mydb`.`employers` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
    );

    -- -----------------------------------------------------
    -- Table `mydb`.`companies`
    -- -----------------------------------------------------

    CREATE TABLE IF NOT EXISTS `mydb`.`companies` 
    (
    `employer_id` INT NOT NULL,
    `rep_nie_dni_pass` VARCHAR(50) NOT NULL,
    `rep_title` VARCHAR(100) NOT NULL,
    PRIMARY KEY (`employer_id`),
    CONSTRAINT `fk_companies_to_employers`
        FOREIGN KEY (`employer_id`)
        REFERENCES `mydb`.`employers` (`id`)
        ON DELETE NO ACTION
        ON UPDATE NO ACTION
    );