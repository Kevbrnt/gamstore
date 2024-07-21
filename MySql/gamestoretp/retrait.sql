create table gamestoretp.retrait
(
    id          int auto_increment
        primary key,
    adresse     varchar(100) not null,
    ville       varchar(100) not null,
    code_postal varchar(100) not null
);

