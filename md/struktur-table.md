nama database : botanic2
table-table,

table: Cabang
id_cabang Primary int(11) AUTO_INCREMENT
nama_cabang varchar(40)
alamat text
gps text
kode_cabang
foto varchar(70)
hp varchar(15)
created_date

table: tipe_kamar
id_tipe int (11) AUTO_INCREMENT Primary
nama_tipe varchar
gambar text
keterangan text

table: cabang_tipe
id_akomodasi int
id_cabang int
id_tipe int
keterangan text
created_date datetime
link_youtube text

# khusus list pakai ini

SELECT
cabang_tipe.id_akomodasi,
cabang_tipe.id_cabang,
cabang.nama_cabang,
cabang_tipe.id_tipe,
tipe_kamar.nama_tipe,
tipe_kamar.gambar,
tipe_kamar.keterangan,
cabang_tipe.keterangan,
cabang_tipe.created_date,
cabang_tipe.link_youtube
FROM
cabang_tipe
INNER JOIN
cabang
ON
cabang_tipe.id_cabang = cabang.id_cabang
INNER JOIN
tipe_kamar
ON
cabang_tipe.id_tipe = tipe_kamar.id_tipe
==================

table: fasilitas
id_fasilitas int primary key auto_increment
id_cabang int
nama_fasilitas varchar
deskripsi text
gambar1 text
gambar2 text
aktif tinyint
status_free tinyint
range_harga float
created_at datetime

# khusus list pakai dibawah ini

SELECT
f.id_fasilitas,
f.id_cabang,
c.nama_cabang,
f.nama_fasilitas,
f.deskripsi,
f.gambar1,
f.gambar2,
f.aktif,
f.status_free,
f.range_harga,
f.created_at
FROM
fasilitas f
INNER JOIN
cabang c
ON
f.id_cabang = c.id_cabang
================================

table : users
id_users int
username varchar
password varchar enkripsi password dengan password_hash() dan password_verify()
aktif tinyint
created_at datetime
last_login datetime

cek login query
SELECT
u.id_users,
u.username,
u.`password`
FROM
users u
where aktif = 1 and username = ? and `password` = ?

=================================

table: guest

id_guest int PK Auto Increment
nama_lengkap varchar
email varchar
wa varchar
kota varchar
aktif tinyint
total_point decimal
created_at datetime
last_login datetime

================================
