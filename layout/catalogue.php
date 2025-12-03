<?php
// layout/catalogue.php - Catalogue chính thức (Danh mục khóa học)
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../controllers/CourseController.php';

$db = (new Database())->connect();
$controller = new CourseController($db);
$allCourses = $controller->model->getAll(); // Lấy tất cả khóa học
$featured = array_slice($allCourses, 0, 9); // Chỉ lấy 9 khóa nổi bật nhất để catalogue đẹp
?>

<style>
  #catalogueOverlay{ position:fixed; inset:0; background:rgba(0,0,0,0.75); backdrop-filter:blur(10px); opacity:0; visibility:hidden; transition:all .4s; z-index:9998; }
  #catalogueOverlay.show{ opacity:1; visibility:visible; }
  #catalogueModal{ transform:scale(0.8); opacity:0; transition:all .5s cubic-bezier(0.34,1.56,0.64,1); pointer-events:none; }
  #catalogueModal.show{ transform:scale(1); opacity:1; pointer-events:auto; }
  .cat-page{ display:none; }
  .cat-page.active{ display:block; animation:slideUp .6s ease-out; }
  @keyframes slideUp{ from{opacity:0; transform:translateY(30px)} to{opacity:1; transform:translateY(0)} }
</style>

<!-- Overlay -->
<div id="catalogueOverlay" onclick="closeCatalogue()"></div>

<!-- Modal Catalogue giữa màn hình -->
<div id="catalogueModal" class="fixed inset-0 z-[9999] flex items-center justify-center hidden">
  <div class="bg-white rounded-3xl shadow-2xl w-full max-w-5xl mx-4 max-h-[92vh] overflow-hidden flex flex-col">

    <!-- Header sang trọng -->
    <div class="bg-gradient-to-r from-violet-600 to-indigo-600 text-white p-8 text-center relative">
      <h2 class="text-4xl font-black tracking-wider">DANH MỤC KHÓA HỌC 2025</h2>
      <p class="text-xl mt-3 opacity-90">Code Cùng Sang – Học thực chiến • Việc làm ngay</p>
      <button onclick="closeCatalogue()" class="absolute top-6 right-8 text-4xl hover:scale-125 transition">&times;</button>
    </div>

    <!-- Nội dung catalogue -->
    <div class="flex-1 overflow-y-auto bg-gradient-to-b from-gray-50 to-white">
      <div id="catPages" class="p-8">

        <!-- Trang 1 – Bìa -->
        <div class="cat-page active text-center py-20">
          <h1 class="text-8xl font-black bg-gradient-to-r from-violet-600 to-indigo-600 bg-clip-text text-transparent">
            CODE CÙNG SANG
          </h1>
          <p class="text-3xl mt-8 text-gray-700">Catalogue các lộ trình học HOT nhất 2025</p>
          <button onclick="nextCat()" class="mt-12 bg-indigo-600 text-white px-12 py-5 rounded-full text-xl font-bold hover:bg-indigo-700 shadow-xl">
            Xem ngay
          </button>
        </div>

        <!-- Các trang khóa học (mỗi trang 3–6 khóa) -->
        <?php 
        $chunks = array_chunk($featured, 6); // chia 6 khóa/trang
        foreach($chunks as $index => $courses): 
        ?>
        <div class="cat-page">
          <h3 class="text-3xl font-bold text-center mb-10 text-indigo-700">
            <?= $index==0 ? 'Lộ trình nổi bật nhất' : 'Tiếp tục khám phá' ?>
          </h3>
          <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
            <?php foreach($courses as $course): ?>
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden hover:shadow-2xl hover:-translate-y-3 transition-all duration-300 border border-indigo-100"
                 onclick="window.location.href='course-detail.php?id=<?= $course['id'] ?>'" style="cursor:pointer">
              <div class="h-40 bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-7xl text-white">
                <?= htmlspecialchars($course['bieu_tuong']) ?>
              </div>
              <div class="p-6">
                <h4 class="font-bold text-xl font-bold text-gray-800 line-clamp-2">
                  <?= htmlspecialchars($course['ten_khoa_hoc']) ?>
                </h4>
                <p class="text-sm text-gray-600 mt-3 line-clamp-3">
                  <?= htmlspecialchars($course['mo_ta']) ?>
                </p>
                <div class="mt-5 flex items-center justify-between">
                  <div>
                    <span class="text-2xl font-black text-indigo-600">
                      <?= number_format($course['gia']) ?>đ
                    </span>
                    <?php if($course['gia'] > 0): ?>
                      <span class="block text-xs text-gray-500 line-through">2.990.000đ</span>
                    <?php endif; ?>
                  </div>
                  <div class="text-right">
                    <div class="text-sm font-semibold"><?= $course['so_hoc_vien'] ?> học viên</div>
                    <div class="text-xs text-gray-500"><?= $course['so_gio_hoc'] ?> giờ</div>
                  </div>
                </div>
                <button class="w-full mt-5 bg-gradient-to-r from-indigo-600 to-violet-600 text-white py-3 rounded-xl font-bold hover:opacity-90 transition">
                  Xem chi tiết
                </button>
              </div>
            </div>
            <?php endforeach; ?>
          </div>
        </div>
        <?php endforeach; ?>

        <!-- Trang cuối – Call to action -->
        <div class="cat-page text-center py-20">
          <h2 class="text-5xl font-black text-indigo-600 mb-8">Bạn đã sẵn sàng chưa?</h2>
          <p class="text-2xl text-gray-700 mb-12">Chọn ngay lộ trình phù hợp và bắt đầu hành trình lập trình thực chiến!</p>
          <a href="category.php" class="inline-block bg-gradient-to-r from-indigo-600 to-violet-600 text-white px-16 py-6 rounded-full text-2xl font-bold hover:shadow-2xl transform hover:scale-105 transition">
            Xem tất cả khóa học
          </a>
        </div>

      </div>
    </div>

    <!-- Thanh điều khiển dưới -->
    <div class="bg-white border-t px-10 py-6 flex justify-between items-center">
      <button onclick="prevCat()" id="catPrev" class="text-indigo-600 font-bold text-lg">Trước</button>
      <div class="flex gap-3">
        <?php for($i = 0; $i < count($chunks) + 2; $i++): ?>
          <span class="w-3 h-3 rounded-full <?= $i===0?'bg-indigo-600':'bg-gray-300' ?> cat-dot"></span>
        <?php endfor; ?>
      </div>
      <button onclick="nextCat()" id="catNext" class="bg-indigo-600 text-white px-10 py-3 rounded-full font-bold">Tiếp</button>
    </div>
  </div>
</div>



<script>
let catIndex = 0;
const catPages = document.querySelectorAll('.cat-page');
const catDots = document.querySelectorAll('.cat-dot');

function showCat(n) {
  catPages.forEach((p,i)=>p.classList.toggle('active', i===n));
  catDots.forEach((d,i)=>{
    d.classList.toggle('bg-indigo-600', i===n);
    d.classList.toggle('bg-gray-300', i!==n);
  });
  document.getElementById('catPrev').style.opacity = n===0 ? 0.5 : 1;
  if (n === catPages.length-1) {
    document.getElementById('catNext').textContent = 'Đóng';
  } else {
    document.getElementById('catNext').textContent = 'Tiếp';
  }
}
function nextCat(){
  if(catIndex < catPages.length-1) catIndex++;
  else closeCatalogue();
  showCat(catIndex);
}
function prevCat(){
  if(catIndex > 0) catIndex--;
  showCat(catIndex);
}
function openCatalogue(){
  document.getElementById('catalogueOverlay').classList.add('show');
  document.getElementById('catalogueModal').classList.remove('hidden');
  document.getElementById('catalogueModal').classList.add('show');
  document.body.style.overflow='hidden';
  catIndex=0; showCat(0);
}
function closeCatalogue(){
  document.getElementById('catalogueOverlay').classList.remove('show');
  document.getElementById('catalogueModal').classList.remove('show');
  setTimeout(()=>document.getElementById('catalogueModal').classList.add('hidden'), 500);
  document.body.style.overflow='';
}
document.addEventListener('keydown', e=> { if(e.key==='Escape') closeCatalogue(); });
</script>