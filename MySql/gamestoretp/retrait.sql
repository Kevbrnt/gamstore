create table gamestoretp.retrait
(
    id          int auto_increment
        primary key,
    adresse     varchar(100) not null,
    ville       varchar(100) not null,
    code_postal varchar(100) not null
);

INSERT INTO retrait (adresse, ville, code_postal) VALUES
                                                      ('Nantes', '5 rue de l\'Arche Sèche', '44000'),
                                                      ('Lille', '271 rue Léon Gambetta', '59000'),
                                                      ('Bordeaux', '12 rue des trois-conils', '33000'),
                                                      ('Paris', '4 boulevard Voltaire', '75011'),
                                                      ('Toulouse', '11 avenue de l\'URSS', '31400');

