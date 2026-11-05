
const API_URL = "https://DOMAIN-RAILWAY-LU.up.railway.app/api/index.php";

const form = document.getElementById("form-data");
const inputId = document.getElementById("id");
const inputNama = document.getElementById("nama");
const tombolSimpan = document.getElementById("tombol-simpan");
const tombolBatal = document.getElementById("tombol-batal");
const daftar = document.getElementById("daftar");
const pesan = document.getElementById("pesan");

async function request(method, data = null) {
    const options = {
        method,
        headers: {
            "Content-Type": "application/json"
        }
    };

    if (data) {
        options.body = JSON.stringify(data);
    }

    const response = await fetch(API_URL, options);
    const result = await response.json();

    if (!response.ok) {
        throw new Error(result.error || "Request gagal");
    }

    return result;
}

function tampilkanPesan(teks) {
    pesan.textContent = teks;
}

async function tampilkanData() {
    try {
        const data = await request("GET");

        daftar.replaceChildren();

        data.forEach(mahasiswa => {
            const baris = document.createElement("tr");

            const kolomId = document.createElement("td");
            kolomId.textContent = mahasiswa.id;

            const kolomNama = document.createElement("td");
            kolomNama.textContent = mahasiswa.nama;

            const kolomAksi = document.createElement("td");
            kolomAksi.className = "aksi";

            const tombolEdit = document.createElement("button");
            tombolEdit.textContent = "Edit";
            tombolEdit.className = "edit";

            tombolEdit.onclick = () => {
                inputId.value = mahasiswa.id;
                inputNama.value = mahasiswa.nama;
                tombolSimpan.textContent = "Update Data";
                tombolBatal.hidden = false;
                inputNama.focus();
            };

            const tombolHapus = document.createElement("button");
            tombolHapus.textContent = "Hapus";
            tombolHapus.className = "hapus";

            tombolHapus.onclick = async () => {
                if (!confirm("Yakin ingin menghapus data?")) return;

                try {
                    await request("DELETE", {
                        id: mahasiswa.id
                    });

                    tampilkanPesan("Data berhasil dihapus");
                    await tampilkanData();

                } catch (error) {
                    tampilkanPesan(error.message);
                }
            };

            kolomAksi.append(tombolEdit, tombolHapus);
            baris.append(kolomId, kolomNama, kolomAksi);
            daftar.appendChild(baris);
        });

    } catch (error) {
        tampilkanPesan(error.message);
    }
}

function resetForm() {
    form.reset();
    inputId.value = "";
    tombolSimpan.textContent = "Tambah Data";
    tombolBatal.hidden = true;
}

form.addEventListener("submit", async event => {
    event.preventDefault();

    const id = inputId.value;
    const nama = inputNama.value.trim();

    if (!nama) return;

    tombolSimpan.disabled = true;

    try {
        if (id) {
            await request("PUT", { id, nama });
            tampilkanPesan("Data berhasil diperbarui");
        } else {
            await request("POST", { nama });
            tampilkanPesan("Data berhasil ditambahkan");
        }

        resetForm();
        await tampilkanData();

    } catch (error) {
        tampilkanPesan(error.message);

    } finally {
        tombolSimpan.disabled = false;
    }
});

tombolBatal.addEventListener("click", resetForm);

tampilkanData();
