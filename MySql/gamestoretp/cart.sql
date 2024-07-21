create table gamestoretp.cart
(
    id       integer serial
        primary key,
    user_id  integer            not null,
    game_id  integer            not null,
    quantity integer            not null,
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

