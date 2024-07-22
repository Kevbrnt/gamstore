-- Création du schéma gamestoretp si nécessaire
CREATE SCHEMA IF NOT EXISTS gamestoretp;

-- Table users
CREATE TABLE gamestoretp.users (
                                   id SERIAL PRIMARY KEY,
                                   username VARCHAR(50) UNIQUE NOT NULL,
                                   first_name VARCHAR(50) NOT NULL,
                                   last_name VARCHAR(50) NOT NULL,
                                   email VARCHAR(100) UNIQUE NOT NULL,
                                   address TEXT NOT NULL,
                                   password VARCHAR(255) NOT NULL,
                                   add_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                   image_url VARCHAR(100) DEFAULT 'asset/profils/default.png' NOT NULL,
                                   role VARCHAR(20) CHECK (role IN ('Visiteur', 'Employés', 'Administrateur')) DEFAULT 'Visiteur' NOT NULL
);

-- Table games
CREATE TABLE gamestoretp.games (
                                   id SERIAL PRIMARY KEY,
                                   name VARCHAR(100) NOT NULL,
                                   price DECIMAL(10, 2) NOT NULL,
                                   promotion_price DECIMAL(10, 2) DEFAULT 0.00,
                                   stock INTEGER DEFAULT 0 NOT NULL,
                                   image_url VARCHAR(255),
                                   add_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                   pegi VARCHAR(2) CHECK (pegi IN ('3', '7', '12', '16', '18')) NOT NULL,
                                   genre VARCHAR(100) NOT NULL,
                                   platform VARCHAR(100) NOT NULL,
                                   Description TEXT NOT NULL
);

-- Table cart
CREATE TABLE gamestoretp.cart (
                                  id SERIAL PRIMARY KEY,
                                  user_id INTEGER NOT NULL,
                                  game_id INTEGER NOT NULL,
                                  quantity INTEGER NOT NULL,
                                  price DECIMAL(10, 2) NOT NULL,
                                  CONSTRAINT cart_ibfk_1 FOREIGN KEY (user_id) REFERENCES gamestoretp.users (id) ON DELETE CASCADE,
                                  CONSTRAINT cart_ibfk_2 FOREIGN KEY (game_id) REFERENCES gamestoretp.games (id) ON DELETE CASCADE
);

CREATE INDEX game_id ON gamestoretp.cart (game_id);
CREATE INDEX user_id ON gamestoretp.cart (user_id);

-- Table retrait
CREATE TABLE gamestoretp.retrait (
                                     id SERIAL PRIMARY KEY,
                                     adresse VARCHAR(100) NOT NULL,
                                     ville VARCHAR(100) NOT NULL,
                                     code_postal VARCHAR(100) NOT NULL
);

-- Table orders
CREATE TABLE gamestoretp.orders (
                                    id SERIAL PRIMARY KEY,
                                    user_id INTEGER NOT NULL,
                                    total_price DECIMAL(10, 2) NOT NULL,
                                    promotion_code VARCHAR(50),
                                    status VARCHAR(10) CHECK (status IN ('VALIDE', 'LIVRE', 'PAYEE')) DEFAULT 'VALIDE',
                                    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
                                    retail_id INTEGER,
                                    date_retrait DATE NOT NULL,
                                    CONSTRAINT orders_ibfk_1 FOREIGN KEY (user_id) REFERENCES gamestoretp.users (id) ON DELETE CASCADE,
                                    CONSTRAINT orders_ibfk_2 FOREIGN KEY (retail_id) REFERENCES gamestoretp.retrait (id)
);

CREATE INDEX retail_id ON gamestoretp.orders (retail_id);
CREATE INDEX user_id ON gamestoretp.orders (user_id);

-- Table order_items
CREATE TABLE gamestoretp.order_items (
                                         id SERIAL PRIMARY KEY,
                                         order_id INTEGER NOT NULL,
                                         game_id INTEGER NOT NULL,
                                         quantity INTEGER NOT NULL,
                                         price DECIMAL(10, 2) NOT NULL,
                                         CONSTRAINT order_items_ibfk_1 FOREIGN KEY (order_id) REFERENCES gamestoretp.orders (id) ON DELETE CASCADE,
                                         CONSTRAINT order_items_ibfk_2 FOREIGN KEY (game_id) REFERENCES gamestoretp.games (id) ON DELETE CASCADE
);

CREATE INDEX game_id ON gamestoretp.order_items (game_id);
CREATE INDEX order_id ON gamestoretp.order_items (order_id);

-- Insertion des données dans la table retrait
INSERT INTO gamestoretp.retrait (adresse, ville, code_postal) VALUES
                                                                  ('5 rue de l''Arche Sèche', 'Nantes', '44000'),
                                                                  ('271 rue Léon Gambetta', 'Lille', '59000'),
                                                                  ('12 rue des trois-conils', 'Bordeaux', '33000'),
                                                                  ('4 boulevard Voltaire', 'Paris', '75011'),
                                                                  ('11 avenue de l''URSS', 'Toulouse', '31400');

