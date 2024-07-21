create table gamestoretp.games
(
    id              int auto_increment
        primary key,
    name            varchar(100)                               not null,
    price           decimal(10, 2)                             not null,
    promotion_price decimal(10, 2) default 0.00                null,
    stock           int            default 0                   not null,
    image_url       varchar(255)                               null,
    add_at          timestamp      default current_timestamp() not null,
    pegi            enum ('3', '7', '12', '16', '18')          not null,
    genre           varchar(100)                               not null,
    platform        varchar(100)                               not null,
    Description     char                                       not null
);

