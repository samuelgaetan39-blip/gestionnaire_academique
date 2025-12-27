CREATE DATABASE IF NOT EXISTS gestionnaire_academique;
USE gestionnaire_academique;

-- Table des utilisateurs (pour l'authentification)
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(191) UNIQUE NOT NULL,
    password VARCHAR(191) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Table des étudiants
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    matricule VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(191) UNIQUE NOT NULL,
    date_of_birth DATE,
    address TEXT,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des enseignants
CREATE TABLE teachers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(191) UNIQUE NOT NULL,
    specialization VARCHAR(100),
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des cours
CREATE TABLE courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) UNIQUE NOT NULL,
    name VARCHAR(191) NOT NULL,
    description TEXT,
    credits INT NOT NULL,
    teacher_id INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE SET NULL
);

-- Table des inscriptions
CREATE TABLE enrollments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    course_id INT NOT NULL,
    enrollment_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (student_id, course_id)
);

-- Table des notes
CREATE TABLE grades (
    id INT AUTO_INCREMENT PRIMARY KEY,
    enrollment_id INT NOT NULL,
    grade DECIMAL(5,2) NOT NULL,
    exam_type VARCHAR(50) NOT NULL,
    date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (enrollment_id) REFERENCES enrollments(id) ON DELETE CASCADE
);

-- Insertion des comptes admin (confidentiels - ne pas afficher)
-- Mot de passe pour tous: 12345678 (hashed: $2y$10$zcIxvqi0dGGuBy.FkMecSOZ0nagdl2zFVRyHZj4pNZYPS9VcB5rXO)

-- Compte admin principal
INSERT INTO users (email, password, role) VALUES 
('admin.ga@gmail.com', '$2y$10$zcIxvqi0dGGuBy.FkMecSOZ0nagdl2zFVRyHZj4pNZYPS9VcB5rXO', 'admin');

-- Compte admin enseignant
INSERT INTO users (email, password, role) VALUES 
('admin.prof.ga@gmail.com', '$2y$10$zcIxvqi0dGGuBy.FkMecSOZ0nagdl2zFVRyHZj4pNZYPS9VcB5rXO', 'teacher');

INSERT INTO teachers (user_id, first_name, last_name, email, specialization, phone) VALUES
(2, 'Enseignant', 'Admin', 'admin.prof.ga@gmail.com', 'Administration', '0600000001');

-- Compte admin étudiant
INSERT INTO users (email, password, role) VALUES 
('admin.etu.ga@gmail.com', '$2y$10$zcIxvqi0dGGuBy.FkMecSOZ0nagdl2zFVRyHZj4pNZYPS9VcB5rXO', 'student');

INSERT INTO students (user_id, matricule, first_name, last_name, email, date_of_birth, address, phone) VALUES
(3, 'ADM001', 'Étudiant', 'Admin', 'admin.etu.ga@gmail.com', '2000-01-01', 'Adresse Admin', '0600000002');

-- Insertion de cours par défaut
INSERT INTO courses (code, name, description, credits, teacher_id) VALUES
('INFO101', 'Introduction à la programmation', 'Concepts de base de la programmation avec Python', 6, 1),
('INFO201', 'Structures de données', 'Étude des structures de données fondamentales', 6, 1),
('MATH101', 'Algèbre linéaire', 'Vecteurs, matrices et espaces vectoriels', 5, 1),
('MATH201', 'Analyse mathématique', 'Calcul différentiel et intégral', 5, 1);