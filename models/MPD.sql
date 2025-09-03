-- ========================
-- Table User
-- ========================
CREATE TABLE user (
    id_user INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(180) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    roles JSON NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    terms_accepted_at DATE,
    avatar_url VARCHAR(255)
);

-- ========================
-- Table Defi
-- ========================
CREATE TABLE defi (
    id_defi INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    date_defi DATE NOT NULL,
    type_defi VARCHAR(50) NOT NULL,
    region VARCHAR(100) NOT NULL,
    country VARCHAR(100) NOT NULL,
    distance FLOAT NOT NULL,
    min_participants INT NOT NULL,
    max_participants INT NOT NULL,
    image_url VARCHAR(255),
    createur_id INT NOT NULL,
    CONSTRAINT fk_defi_user FOREIGN KEY (createur_id) REFERENCES user (id_user)
);

-- ========================
-- Table Inscription
-- ========================
CREATE TABLE inscription (
    id_inscription INT AUTO_INCREMENT PRIMARY KEY,
    date_inscription DATETIME NOT NULL,
    user_id INT NOT NULL,
    defi_id INT NOT NULL,
    CONSTRAINT fk_inscription_user FOREIGN KEY (user_id) REFERENCES user (id_user),
    CONSTRAINT fk_inscription_defi FOREIGN KEY (defi_id) REFERENCES defi (id_defi),
    CONSTRAINT uq_inscription UNIQUE (user_id, defi_id) -- un user ne peut s’inscrire qu’une fois à un défi
);

-- ========================
-- Table Commentaire
-- ========================
CREATE TABLE commentaire (
    id_commentaire INT AUTO_INCREMENT PRIMARY KEY,
    contenu TEXT NOT NULL,
    date_commentaire DATETIME NOT NULL,
    user_id INT NOT NULL,
    defi_id INT NOT NULL,
    CONSTRAINT fk_commentaire_user FOREIGN KEY (user_id) REFERENCES user (id_user),
    CONSTRAINT fk_commentaire_defi FOREIGN KEY (defi_id) REFERENCES defi (id_defi)
);

-- ========================
-- Table Like
-- ========================
CREATE TABLE `like` (
    id_like INT AUTO_INCREMENT PRIMARY KEY,
    date_like DATETIME NOT NULL,
    user_id INT NOT NULL,
    defi_id INT NOT NULL,
    CONSTRAINT fk_like_user FOREIGN KEY (user_id) REFERENCES user (id_user),
    CONSTRAINT fk_like_defi FOREIGN KEY (defi_id) REFERENCES defi (id_defi),
    CONSTRAINT uq_like UNIQUE (user_id, defi_id) -- un user ne peut liker qu’une fois un défi
);

-- ========================
-- Table Notification
-- ========================
CREATE TABLE notification (
    id_notification INT AUTO_INCREMENT PRIMARY KEY,
    message VARCHAR(255) NOT NULL,
    date_notification DATETIME NOT NULL,
    lu BOOLEAN DEFAULT FALSE,
    user_id INT NOT NULL,
    CONSTRAINT fk_notification_user FOREIGN KEY (user_id) REFERENCES user (id_user)
);
