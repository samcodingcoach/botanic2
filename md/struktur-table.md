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
password varchar
kota varchar
aktif tinyint
total_point decimal
created_at datetime
last_login datetime

================================

table: front_office
id_fo int
wa varchar
id_cabang int
aktif tinyint

query khusus list.php pakai ini
SELECT
front_office.id_fo,
front_office.wa,
front_office.id_cabang,
cabang.nama_cabang,
front_office.aktif
FROM
front_office
INNER JOIN
cabang
ON
front_office.id_cabang = cabang.id_cabang
=================================

table: hk

id_hk int 11
kode_hk varchar 25
id_cabang int 11
jabatan varchar 20
nama_lengkap varchar 50
jenis_kelamin tinyint 4
wa varchar 16
aktif tinyint 4
created_date datetime

query list.php
SELECT
hk.id_hk,
hk.kode_hk,
cabang.nama_cabang,
hk.id_cabang,
hk.jabatan,
hk.nama_lengkap,
hk.jenis_kelamin,
hk.wa,
hk.aktif,
hk.created_date
FROM
cabang
INNER JOIN
hk
ON
cabang.id_cabang = hk.id_cabang
=================================

table: halaman
id_halaman int primary key auto_increment
id_users int
id_cabang int
nama_halaman varchar
link text
username varchar
created_date datetime
logo text
aktif tinyint

query list.php
SELECT
halaman.id_halaman,
halaman.id_users,
users.username,
halaman.id_cabang,
cabang.nama_cabang,
halaman.nama_halaman,
halaman.link,
halaman.username as username_halaman,
halaman.created_date,
halaman.logo,
halaman.aktif
FROM
halaman
INNER JOIN
users
ON
halaman.id_users = users.id_users
INNER JOIN
cabang
ON
halaman.id_cabang = cabang.id_cabang
=================================

table: near_area
id_area int PK Auto Increment
id_cabang int
nama_area varchar
jenis_area varchar
alamat text
gps varchar
jarak varchar
foto text <- ini foto ukuran harus 512x512 dan tersimpan di images/near/
aktif varchar
created_date datetime

query list.php
SELECT
near_area.id_area,
near_area.id_cabang,
cabang.nama_cabang,
near_area.nama_area,
near_area.jenis_area,
near_area.alamat,
near_area.gps,
near_area.jarak,
near_area.foto,
near_area.aktif,
near_area.created_date
FROM
near_area
INNER JOIN
cabang
ON
near_area.id_cabang = cabang.id_cabang

=======================

table: inap
id_inap int pk auto increment
id_cabang int
id_akomodasi int
id_guest int
kode_booking varchar
nomor_kamar varchar
tanggal_in datetime
tanggal_out datetime
status tinyint 0=staying 1=completed
ota varchar
link_receipt

query list

SELECT
inap.id_inap,
inap.id_cabang,
cabang.nama_cabang,
inap.id_akomodasi,
tipe_kamar.nama_tipe,
inap.id_guest,
guest.nama_lengkap,
inap.kode_booking,
inap.nomor_kamar,
inap.tanggal_in,
inap.tanggal_out,
inap.`status`,
inap.ota,
inap.link_receipt,
inap.created_date,
inap.id_users,
users.username
FROM
inap
INNER JOIN
cabang
ON
inap.id_cabang = cabang.id_cabang
INNER JOIN
cabang_tipe
ON
inap.id_akomodasi = cabang_tipe.id_akomodasi
INNER JOIN
tipe_kamar
ON
cabang_tipe.id_tipe = tipe_kamar.id_tipe
INNER JOIN
guest
ON
inap.id_guest = guest.id_guest
INNER JOIN
users
ON
inap.id_users = users.id_users
================================
