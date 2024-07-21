create table gamestoretp.users
(
    id         int auto_increment
        primary key,
    username   varchar(50)                                                                         not null,
    first_name varchar(50)                                                                         not null,
    last_name  varchar(50)                                                                         not null,
    email      varchar(100)                                                                        not null,
    address    text                                                                                not null,
    password   varchar(255)                                                                        not null,
    created_at timestamp                                       default current_timestamp()         not null,
    image_url  varchar(100)                                    default 'asset/profils/default.png' not null,
    role       enum ('Visiteur', 'Employés', 'Administrateur') default 'Visiteur'                  not null,
    constraint email
        unique (email),
    constraint username
        unique (username)
);

