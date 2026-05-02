--
-- PostgreSQL database dump
--

\restrict 6VJWzVs6lgECYTNLfvqwy10cx7PVJTjJNjUQz4d3nYFcpkYOusGl3Xu5vfLGrs5

-- Dumped from database version 15.17
-- Dumped by pg_dump version 15.17

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: cache; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration bigint NOT NULL
);


--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration bigint NOT NULL
);


--
-- Name: car_views; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.car_views (
    id bigint NOT NULL,
    car_id bigint NOT NULL,
    view_date date NOT NULL,
    count integer DEFAULT 0 NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: car_views_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.car_views_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: car_views_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.car_views_id_seq OWNED BY public.car_views.id;


--
-- Name: cars; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.cars (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    brand character varying(255) NOT NULL,
    model character varying(255) NOT NULL,
    year integer NOT NULL,
    description text NOT NULL,
    city character varying(255) NOT NULL,
    price_per_day numeric(10,2) NOT NULL,
    buyout_price numeric(12,2),
    photos json,
    is_available boolean DEFAULT true NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: cars_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.cars_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: cars_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.cars_id_seq OWNED BY public.cars.id;


--
-- Name: conversations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.conversations (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    car_id bigint,
    renter_id bigint,
    owner_id bigint
);


--
-- Name: conversations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.conversations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: conversations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.conversations_id_seq OWNED BY public.conversations.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


--
-- Name: jobs; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: messages; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.messages (
    id bigint NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    conversation_id bigint,
    user_id bigint,
    body text,
    is_read boolean DEFAULT false
);


--
-- Name: messages_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.messages_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: messages_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.messages_id_seq OWNED BY public.messages.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


--
-- Name: personal_access_tokens; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.personal_access_tokens (
    id bigint NOT NULL,
    tokenable_type character varying(255) NOT NULL,
    tokenable_id bigint NOT NULL,
    name text NOT NULL,
    token character varying(64) NOT NULL,
    abilities text,
    last_used_at timestamp(0) without time zone,
    expires_at timestamp(0) without time zone,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.personal_access_tokens_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.personal_access_tokens_id_seq OWNED BY public.personal_access_tokens.id;


--
-- Name: phone_verifications; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.phone_verifications (
    id bigint NOT NULL,
    user_id bigint NOT NULL,
    phone character varying(255) NOT NULL,
    code character varying(255) NOT NULL,
    expires_at timestamp(0) without time zone NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


--
-- Name: phone_verifications_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.phone_verifications_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: phone_verifications_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.phone_verifications_id_seq OWNED BY public.phone_verifications.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


--
-- Name: users; Type: TABLE; Schema: public; Owner: -
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    phone character varying(255),
    phone_verified_at timestamp(0) without time zone,
    is_admin boolean DEFAULT false,
    avatar character varying(255)
);


--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: -
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: -
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: car_views id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.car_views ALTER COLUMN id SET DEFAULT nextval('public.car_views_id_seq'::regclass);


--
-- Name: cars id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cars ALTER COLUMN id SET DEFAULT nextval('public.cars_id_seq'::regclass);


--
-- Name: conversations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conversations ALTER COLUMN id SET DEFAULT nextval('public.conversations_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: messages id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.messages ALTER COLUMN id SET DEFAULT nextval('public.messages_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: personal_access_tokens id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens ALTER COLUMN id SET DEFAULT nextval('public.personal_access_tokens_id_seq'::regclass);


--
-- Name: phone_verifications id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.phone_verifications ALTER COLUMN id SET DEFAULT nextval('public.phone_verifications_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cache (key, value, expiration) FROM stdin;
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: car_views; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.car_views (id, car_id, view_date, count, created_at, updated_at) FROM stdin;
3	2	2026-04-23	2	2026-04-23 09:34:41	2026-04-23 09:34:41
8	2	2026-04-24	3	2026-04-24 08:04:10	2026-04-24 08:04:23
7	4	2026-04-24	10	2026-04-24 01:53:53	2026-04-24 08:04:41
6	3	2026-04-24	20	2026-04-24 01:35:28	2026-04-24 09:52:27
4	3	2026-04-23	5	2026-04-23 09:34:58	2026-04-23 15:36:44
1	4	2026-04-23	7	2026-04-23 09:34:29	2026-04-23 16:23:55
2	5	2026-04-23	13	2026-04-23 09:34:35	2026-04-23 16:46:57
9	6	2026-04-24	8	2026-04-24 09:59:15	2026-04-24 09:59:30
5	5	2026-04-24	21	2026-04-24 01:35:19	2026-04-24 10:00:11
\.


--
-- Data for Name: cars; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.cars (id, user_id, brand, model, year, description, city, price_per_day, buyout_price, photos, is_available, created_at, updated_at) FROM stdin;
6	7	BMW	X3	2019	Аренда с выкупом.\r\n\r\nBMW X3 M 3.0 AT, 2019, 128 986 км\r\n\r\nАванс 2500 000 рублей\r\n\r\nСрок 36 месяцев	Челябинск	6900.00	10000000.00	["\\/storage\\/cars\\/KeGCATkFRd28pvNv5tOqDFcA9aToyam9C3JWBr4p.jpg","\\/storage\\/cars\\/ry1nOYZQI1bLMyzRlVxuTVtp4fUHcZUXQMygCnOq.jpg"]	t	2026-04-24 09:58:47	2026-04-24 09:59:28
3	7	Geely	Citiray	2024	Сегодня Вы можете стать владельцем этого автомобиля всего лишь за 100 000 рублей в месяц! 🌛 без аванса.\r\nЛибо за 50000 рублей в месяц внеся аванс!\r\nЛибо 4000р в сутки с авансом 50 000р\r\n\r\nЧто есть аренда с правом выкупа - это покупка автомобиля в рассрочку. Заключается договор по которому вы становитесь владельцем автомобиля и в конце срока выкупа птс переоформляется на Вас.\r\n\r\nGeely cityray 2024г\r\n‎Пробег 80 000+ км\r\n‎Максимальная комплектация\r\n‎Панорамная крыша и люк.\r\n‎Адаптивный круиз контроль\r\n‎Датчики слепых зон, дождя и света.\r\n‎Ассистенты удержания в полосе,\r\n‎Парковочный ассистент,\r\n‎Автоматическое торможение.\r\n‎Подогревы четырех сидений, руля, лобового стекла и зеркал.\r\n‎Открытие и закрытие багажника с брелка, планшета или кнопки на багажнике!\r\n‎Электро регулировка передних сидений.\r\n‎Беспроводная зарядка телефона.\r\n‎\r\n‎Комплект зимней и летней резины.\r\n\r\nУсловия выкупа\r\nОт 50 000 рублей до 120 000 в месяц\r\n\r\nДля договора аренды с выкупом потребуется:\r\nПаспорт\r\nВодительское удостоверение\r\n\r\nДосрочное погашение приветствуется ❗\r\n\r\nАвтомобиль в наличии и ожидает Вас г. Челябинск ул. Кудрявцева 32а	Челябинск	4000.00	1000.00	["\\/storage\\/cars\\/8wnRpoZkLPnioFErCCvOfcSvNkp6c2hEKUifUrqD.jpg","\\/storage\\/cars\\/P1f4xVeitidwY6ujlbbsiOYMKmExHJeLArWt1yF8.jpg"]	t	2026-04-21 16:53:51	2026-04-21 16:53:51
2	7	ЛАДА	2107	2007	ВАЗ (LADA) 2114 Samara 1.6 MT, 2007, 250 000 км\r\nУсловия выкупа:\r\nСрок выкупа от 50 дней до 1 года.\r\n\r\nНа Ваш запрос я отправлю цены и сроки.\r\n\r\nДля договора паспорт и водительское.\r\nПосле подписания договора Вы становитесь Владельцем автомобиля и все расходы по его обслуживанию берете на себя!\r\n\r\nВы можете предложить свой вариант автомобиля который продается по Челябинску. У вас есть аванс не менее 30%.	Челябинск	500.00	1000.00	["\\/storage\\/cars\\/e1yYR5GpTm3cCoaruOD8Cznf9JqmsEMgNJHf2jdt.jpg"]	t	2026-04-21 08:40:15	2026-04-21 17:16:15
4	7	Audi	Q7	2025	Audi Q7 3.0 AT, 2025\r\n\r\nАванс 50%	Челябинск	9500.00	20000000.00	["\\/storage\\/cars\\/iqzzQU2j3H3IFopPJ5piRlucYFIAWUzlkHoOBV3Y.jpg"]	t	2026-04-22 17:50:16	2026-04-22 17:50:16
5	7	Mercedes-Benz	E-200	2025	Mercedes-Benz E-класс 2.0 AT, 2025, 1 160 км\r\n\r\nАванс 50%	Челябинск	8000.00	149999999.98	["\\/storage\\/cars\\/u8dVvfohtvkTeeaJwxDWnOdoOZhMCpTj51I8VLfC.jpg"]	t	2026-04-22 18:06:32	2026-04-22 18:06:32
\.


--
-- Data for Name: conversations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.conversations (id, created_at, updated_at, car_id, renter_id, owner_id) FROM stdin;
3	2026-04-23 01:08:02	2026-04-23 01:08:02	4	9	7
4	2026-04-24 07:48:10	2026-04-24 07:48:10	5	9	7
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: messages; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.messages (id, created_at, updated_at, conversation_id, user_id, body, is_read) FROM stdin;
7	2026-04-23 01:08:10	2026-04-23 09:28:43	3	9	привет	t
8	2026-04-24 07:48:21	2026-04-24 10:01:47	4	9	мерс еще актуален	t
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2026_04_20_153220_create_personal_access_tokens_table	2
5	2026_04_20_153447_add_phone_to_users_table	3
6	2026_04_20_155420_create_phone_verifications_table	4
7	2026_04_21_010709_create_cars_table	5
8	2026_04_21_095411_create_conversations_table	6
9	2026_04_21_095411_create_messages_table	6
10	2026_04_23_011304_create_car_views_table	7
11	2026_04_24_080854_add_avatar_to_users_table	8
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: personal_access_tokens; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.personal_access_tokens (id, tokenable_type, tokenable_id, name, token, abilities, last_used_at, expires_at, created_at, updated_at) FROM stdin;
1	App\\Models\\User	5	api_token	71abf15a989587d25d36c84ffb56568d2d3a12c6626e42d3d60ede84ca24ca10	["*"]	\N	\N	2026-04-20 18:09:35	2026-04-20 18:09:35
33	App\\Models\\User	7	api_token	35824a942720e534ecfdf59819a82629c3546fd5b88f68fa5330d8a644fbdce6	["*"]	2026-04-24 09:35:58	\N	2026-04-24 09:22:18	2026-04-24 09:35:58
2	App\\Models\\User	6	api_token	f6af86ba34118fe4e6fd0f13877c091f70f46f4f5218ee127374ac89c3880956	["*"]	2026-04-21 01:38:46	\N	2026-04-20 18:11:04	2026-04-21 01:38:46
3	App\\Models\\User	7	api_token	35845247916efc7a7e15659962af84ccd72aadf70b2d1a50bb269748721178e5	["*"]	2026-04-21 08:40:15	\N	2026-04-21 08:36:52	2026-04-21 08:40:15
31	App\\Models\\User	7	api_token	e7ea224a93aa1a470e15a79aa28ef6fbca9c2a0f3d01fc0643090c57272e7032	["*"]	2026-04-24 08:52:52	\N	2026-04-24 08:37:51	2026-04-24 08:52:52
24	App\\Models\\User	7	api_token	90e46197227092fda09ad8b2a6c2b5143bd35e7d8a411af2b4b0817692ba230c	["*"]	2026-04-23 16:46:16	\N	2026-04-23 16:46:15	2026-04-23 16:46:16
5	App\\Models\\User	7	api_token	f48159874c813495e1fc96433c304a34fe09287cacce84355f9261b71ecb0a58	["*"]	2026-04-21 17:16:15	\N	2026-04-21 17:09:02	2026-04-21 17:16:15
18	App\\Models\\User	9	api_token	a6d659c9f0a770ced7747e182523fb4807a534847d4507e79f4e38ff6feeaa92	["*"]	2026-04-23 09:20:24	\N	2026-04-23 02:06:39	2026-04-23 09:20:24
32	App\\Models\\User	7	api_token	bc34c4b238b690942b65423d9fef7d6149c5fe8b18f8e2c0e966547c76e77de5	["*"]	2026-04-24 09:16:19	\N	2026-04-24 08:52:53	2026-04-24 09:16:19
28	App\\Models\\User	7	api_token	328882343fd93aadf34b00b6d2cc1f2e398303332493a59b7d63d647c9e9acd9	["*"]	2026-04-24 08:04:53	\N	2026-04-24 07:48:29	2026-04-24 08:04:53
25	App\\Models\\User	7	api_token	2689ee0bf770a3e15274a9b48ccc92293144cce9bc29de407e3cec3f04db41d2	["*"]	2026-04-24 07:44:00	\N	2026-04-23 16:46:16	2026-04-24 07:44:00
29	App\\Models\\User	7	api_token	fc5d07491edb9783c7a39aa054b766104ba2d142b15f9a6f288e1745cccbf43b	["*"]	2026-04-24 08:36:16	\N	2026-04-24 08:34:24	2026-04-24 08:36:16
34	App\\Models\\User	7	api_token	06642e9ed1f2fa1da3ec4bf32ae89361f37c4c40780095d1d9473da520df4e7e	["*"]	2026-04-24 10:01:51	\N	2026-04-24 09:43:05	2026-04-24 10:01:51
35	App\\Models\\User	7	api_token	197e744a42f552330a3e19a942f3b25e5fa6f10a0f70bc15e01e427e696a6c4b	["*"]	2026-04-27 00:55:47	\N	2026-04-27 00:55:46	2026-04-27 00:55:47
\.


--
-- Data for Name: phone_verifications; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.phone_verifications (id, user_id, phone, code, expires_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
DinTviZvMlt37DTf5v6HrOTZmqPlRXV8hMAwC2Eg	7	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 YaBrowser/26.3.0.0 Safari/537.36	eyJfdG9rZW4iOiJpWWlBMEVSWTF0NDJnSjllWFR4aTVCNjhEcDhpekV2MWdxYzlvajdEIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pblwvbWVzc2FnZXMiLCJyb3V0ZSI6ImFkbWluLm1lc3NhZ2VzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjd9	1776906684
vbnB9ykViZ3NEhuAAmXNo0IBUay9y99T3V5nX2pL	7	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 YaBrowser/26.3.0.0 Safari/537.36	eyJfdG9rZW4iOiI0UmhsUGxjM002U0h6aG1UZkgyMFFhZDlzeTlJZGp5T2E1M3FVS1h2IiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pblwvbWVzc2FnZXMiLCJyb3V0ZSI6ImFkbWluLm1lc3NhZ2VzLmluZGV4In0sIl9mbGFzaCI6eyJvbGQiOltdLCJuZXciOltdfSwibG9naW5fd2ViXzU5YmEzNmFkZGMyYjJmOTQwMTU4MGYwMTRjN2Y1OGVhNGUzMDk4OWQiOjd9	1776935565
01Q7vQ6lHdSZeOMqM8px3xyWxPNbtTujZ1OSEB5K	\N	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 YaBrowser/26.3.0.0 Safari/537.36	eyJfdG9rZW4iOiJDbUhHa1pGRTJWbmZRTzR3NU1BM1dYelhyaEI1YjZUOTdhN0w2bVNVIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cL2xvY2FsaG9zdDo4MDAwIiwicm91dGUiOm51bGx9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX19	1776957196
xob30tI6rHnufxkpzjx9xW1CKHRjkR6erHkQ2PmV	7	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 YaBrowser/26.3.0.0 Safari/537.36	eyJfdG9rZW4iOiIxbFdYaDZ1MUloTmd4QXNZMmRzelVCQURFMlFLMEt0MmZrVkJPVzlEIiwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9wcm9maWxlIiwicm91dGUiOiJwcm9maWxlLmVkaXQifSwiX2ZsYXNoIjp7Im9sZCI6W10sIm5ldyI6W119LCJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI6N30=	1777023091
gtZuXtfXu8p2R4ckWuDjlKiASr37JbbT9tsuH9rV	7	127.0.0.1	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/144.0.0.0 YaBrowser/26.3.0.0 Safari/537.36	eyJfdG9rZW4iOiJ2bzlwbWY5aVByZFhQeW56ZFJBcjZscVhJR1BlR1l1OVYzWVJyMjRJIiwidXJsIjpbXSwiX3ByZXZpb3VzIjp7InVybCI6Imh0dHA6XC9cLzEyNy4wLjAuMTo4MDAwXC9hZG1pblwvY2FycyIsInJvdXRlIjoiYWRtaW4uY2Fycy5pbmRleCJ9LCJfZmxhc2giOnsib2xkIjpbXSwibmV3IjpbXX0sImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjo3fQ==	1777251720
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: -
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at, phone, phone_verified_at, is_admin, avatar) FROM stdin;
9	Тест	test@test.test	\N	$2y$12$/roCVKqvCNYibeoSugSJ/eyXBuabx4VfW5KIkrOmqZMPMfItm8zYi	\N	2026-04-23 01:07:02	2026-04-23 01:07:02	+79999999999	\N	f	\N
7	Кафеев Рустам Рамильевич	krr12@mail.ru	\N	$2y$12$OdhAFwp4AnvD7plVQml9/umyR9N0eRfq2OUaDOiCx3dYTRWSXsAxa	bXStt01GSaq38OzGmF8QL0mYMALtE33yFtkNZXS21w8LCkN2bI4Qt1uWhRiA	2026-04-21 08:36:52	2026-04-24 09:35:57	+79049731180	\N	t	/storage/avatars/zp5kmIMNt6lLR086slPEwgMvShMX7dsVoxb6ZzaJ.jpg
\.


--
-- Name: car_views_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.car_views_id_seq', 9, true);


--
-- Name: cars_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.cars_id_seq', 6, true);


--
-- Name: conversations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.conversations_id_seq', 4, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: messages_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.messages_id_seq', 8, true);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.migrations_id_seq', 11, true);


--
-- Name: personal_access_tokens_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.personal_access_tokens_id_seq', 35, true);


--
-- Name: phone_verifications_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.phone_verifications_id_seq', 2, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: -
--

SELECT pg_catalog.setval('public.users_id_seq', 9, true);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: car_views car_views_car_id_view_date_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.car_views
    ADD CONSTRAINT car_views_car_id_view_date_unique UNIQUE (car_id, view_date);


--
-- Name: car_views car_views_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.car_views
    ADD CONSTRAINT car_views_pkey PRIMARY KEY (id);


--
-- Name: cars cars_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cars
    ADD CONSTRAINT cars_pkey PRIMARY KEY (id);


--
-- Name: conversations conversations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: messages messages_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: personal_access_tokens personal_access_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_pkey PRIMARY KEY (id);


--
-- Name: personal_access_tokens personal_access_tokens_token_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.personal_access_tokens
    ADD CONSTRAINT personal_access_tokens_token_unique UNIQUE (token);


--
-- Name: phone_verifications phone_verifications_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.phone_verifications
    ADD CONSTRAINT phone_verifications_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_phone_unique; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_phone_unique UNIQUE (phone);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: cache_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_expiration_index ON public.cache USING btree (expiration);


--
-- Name: cache_locks_expiration_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX cache_locks_expiration_index ON public.cache_locks USING btree (expiration);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: personal_access_tokens_expires_at_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX personal_access_tokens_expires_at_index ON public.personal_access_tokens USING btree (expires_at);


--
-- Name: personal_access_tokens_tokenable_type_tokenable_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX personal_access_tokens_tokenable_type_tokenable_id_index ON public.personal_access_tokens USING btree (tokenable_type, tokenable_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: -
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: car_views car_views_car_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.car_views
    ADD CONSTRAINT car_views_car_id_foreign FOREIGN KEY (car_id) REFERENCES public.cars(id) ON DELETE CASCADE;


--
-- Name: cars cars_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.cars
    ADD CONSTRAINT cars_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: conversations conversations_car_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_car_id_fkey FOREIGN KEY (car_id) REFERENCES public.cars(id) ON DELETE CASCADE;


--
-- Name: conversations conversations_owner_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_owner_id_fkey FOREIGN KEY (owner_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: conversations conversations_renter_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.conversations
    ADD CONSTRAINT conversations_renter_id_fkey FOREIGN KEY (renter_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: messages messages_conversation_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_conversation_id_fkey FOREIGN KEY (conversation_id) REFERENCES public.conversations(id) ON DELETE CASCADE;


--
-- Name: messages messages_user_id_fkey; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.messages
    ADD CONSTRAINT messages_user_id_fkey FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- Name: phone_verifications phone_verifications_user_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: -
--

ALTER TABLE ONLY public.phone_verifications
    ADD CONSTRAINT phone_verifications_user_id_foreign FOREIGN KEY (user_id) REFERENCES public.users(id) ON DELETE CASCADE;


--
-- PostgreSQL database dump complete
--

\unrestrict 6VJWzVs6lgECYTNLfvqwy10cx7PVJTjJNjUQz4d3nYFcpkYOusGl3Xu5vfLGrs5

