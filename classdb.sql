/* =========================================================
   FILE: classdb.sql
   PURPOSE:
   Creates the database and all required tables for the
   Student Records System.
   ========================================================= */

-- ---------------------------------------------------------
-- Create and select database
-- ---------------------------------------------------------
CREATE DATABASE IF NOT EXISTS it3e
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE it3e;

-- ---------------------------------------------------------
-- Drop tables in reverse dependency order
-- ---------------------------------------------------------
DROP TABLE IF EXISTS examsDetails;
DROP TABLE IF EXISTS course;
DROP TABLE IF EXISTS registration;

-- ---------------------------------------------------------
-- Table: registration
-- Stores student registration and login information
-- ---------------------------------------------------------
CREATE TABLE registration (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fname VARCHAR(30) NOT NULL,
    sname VARCHAR(30) NOT NULL,
    gender ENUM('MALE', 'FEMALE') NOT NULL,
    langSpoken VARCHAR(255) DEFAULT NULL,
    dob DATE NOT NULL,
    slevel CHAR(3) NOT NULL,
    emailAdd VARCHAR(150) NOT NULL UNIQUE,
    numOfCourses INT NOT NULL DEFAULT 0,
    wgt DECIMAL(5,2) DEFAULT NULL,
    hgt DECIMAL(4,2) DEFAULT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    pword VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ---------------------------------------------------------
-- Table: course
-- Stores available courses
-- ---------------------------------------------------------
CREATE TABLE course (
    ccode CHAR(7) PRIMARY KEY,
    cname VARCHAR(100) NOT NULL,
    chr INT NOT NULL
);

-- ---------------------------------------------------------
-- Table: examsDetails
-- Stores student exam records
-- ---------------------------------------------------------
CREATE TABLE examsDetails (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ccode CHAR(7) NOT NULL,
    studID INT NOT NULL,
    sem ENUM('Semester 1', 'Semester 2') NOT NULL,
    yr CHAR(4) NOT NULL,
    iaMarks DECIMAL(5,2) NOT NULL DEFAULT 0,
    examMarks DECIMAL(5,2) NOT NULL DEFAULT 0,

    CONSTRAINT uq_exam_record UNIQUE (ccode, studID, sem, yr),

    CONSTRAINT fk_exam_course
        FOREIGN KEY (ccode)
        REFERENCES course(ccode)
        ON UPDATE CASCADE
        ON DELETE RESTRICT,

    CONSTRAINT fk_exam_student
        FOREIGN KEY (studID)
        REFERENCES registration(id)
        ON UPDATE CASCADE
        ON DELETE RESTRICT
);