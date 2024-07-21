create table gamestoretp.orders
(
    id             integer serial
        primary key,
    user_id        integer                                                           not null,
    total_price    decimal(10, 2)                                                not null,
    promotion_code varchar(50)                                                   null,
    status         enum ('VALIDE', 'LIVRE', 'PAYEE') default 'VALIDE'            null,
    created_at     timestamp                         default current_timestamp() not null,
    retail_id      integer                                                           null,
    date_retrait   date                                                          not null,
    constraint orders_ibfk_1
        foreign key (user_id) references gamestoretp.users (id)
            on delete cascade,
    constraint orders_ibfk_2
        foreign key (retail_id) references gamestoretp.retrait (id)
);

create index retail_id
    on gamestoretp.orders (retail_id);

create index user_id
    on gamestoretp.orders (user_id);

