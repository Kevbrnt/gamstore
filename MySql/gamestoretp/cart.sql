create table gamestoretp.cart
(
    id       int auto_increment
        primary key,
    user_id  int            not null,
    game_id  int            not null,
    quantity int            not null,
    price    decimal(10, 2) not null,
    constraint cart_ibfk_1
        foreign key (user_id) references gamestoretp.users (id)
            on delete cascade,
    constraint cart_ibfk_2
        foreign key (game_id) references gamestoretp.games (id)
            on delete cascade
);

create index game_id
    on gamestoretp.cart (game_id);

create index user_id
    on gamestoretp.cart (user_id);

