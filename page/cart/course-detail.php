function enrollCourse(id) {
  const course = courses.find(c => c.id === id);
  
  fetch('add-to-cart.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: `id=${course.id}&name=${encodeURIComponent(course.name)}&price=${encodeURIComponent(course.price)}`
  })
  .then(res => res.json())
  .then(data => {
    if (data.status === 'added') {
      alert(`Đã thêm "${course.name}" vào giỏ hàng!`);
    } else if (data.status === 'exists') {
      alert(`"${course.name}" đã có trong giỏ hàng.`);
    } else {
      alert('Lỗi khi thêm khóa học!');
    }
  });
}
