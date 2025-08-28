Anda adalah expert 3D artist sekaligus developer interaktif. Tugas Anda adalah mengubah maskot login dari boneka sebelumnya menjadi seekor kepiting lucu, imut, dan menggemaskan yang tampil di halaman login web.

🎯 Tujuan
	•	Membuat maskot kepiting 3D dengan gaya kartun (chibi/cute style).
	•	Lengkap dengan anatomi sederhana tapi jelas: capit kanan & kiri, kaki, badan bulat, mata besar ekspresif, senyum lucu.
	•	Ukuran ringan (optimal untuk web, total aset < 2 MB).
	•	Mendukung animasi interaktif sesuai state login.

📦 Output yang diharapkan
	•	File model utama: mascot-crab.glb (format GLB/GLTF).
	•	Satu file berisi semua animasi clip.
	•	Optimisasi: tekstur dikompresi (BasisU/KTX2), mesh dengan Draco compression.

🎬 Animasi yang diperlukan
	1.	Idle → kepiting diam, mengedipkan mata, capit goyang sedikit.
	2.	Typing/Input → capit bergerak cepat seperti sedang mengetik atau menulis.
	3.	Error → kepiting mengangkat capit sambil geleng kepala atau mata X (ekspresi salah).
	4.	Loading → kepiting berputar di tempat atau mengangkat capit seperti memutar roda.
	5.	Success → kepiting melompat kecil ke atas dengan ekspresi gembira dan capit terbuka.

⚙️ Integrasi Teknis
	•	Model kompatibel dengan Three.js atau react-three-fiber.
	•	Semua animasi dikemas dalam satu GLB, dengan clip names: Idle, Typing, Error, Loading, Success.
	•	Ukuran optimal: ≤ 10k polygons, tekstur ≤ 1024px.
	•	Gunakan rig sederhana (skeleton + morph target untuk ekspresi wajah).

🎨 Style Visual
	•	Warna utama: merah kepiting cerah, sedikit glossy.
	•	Mata besar hitam dengan highlight putih agar ekspresif.
	•	Gaya “kartun/anak-anak” (seperti karakter Pixar/Disney mini).