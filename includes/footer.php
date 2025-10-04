</main>
    </div>
    
    <!-- Footer -->
    <?php if (isset($_SESSION['user_id'])): ?>
    <footer class="bg-gray-800 text-white mt-auto">
        <div class="container mx-auto px-4 py-4 text-center">
            <p class="text-sm">&copy; <?php echo date('Y'); ?> ระบบส่งแผนการจัดการเรียนรู้ - พัฒนาโดย โรงเรียนแก่นนครวิทยาลัย</p>
        </div>
    </footer>
    <?php endif; ?>
    
    <!-- Scripts -->
    <script>
        // แสดงข้อความแจ้งเตือน
        function showAlert(message, type = 'success') {
            const alertDiv = document.createElement('div');
            const bgColor = type === 'success' ? 'bg-green-500' : 'bg-red-500';
            
            alertDiv.className = `fixed top-4 right-4 ${bgColor} text-white px-6 py-4 rounded-lg shadow-lg z-50 animate-fade-in`;
            alertDiv.innerHTML = `
                <div class="flex items-center space-x-3">
                    <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} text-xl"></i>
                    <span>${message}</span>
                    <button onclick="this.parentElement.parentElement.remove()" class="ml-4 text-white hover:text-gray-200">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            `;
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.remove();
            }, 5000);
        }
        
        // ยืนยันการลบ
        function confirmDelete(message = 'คุณแน่ใจหรือไม่ว่าต้องการลบ?') {
            return confirm(message);
        }
        
        // แสดง Loading
        function showLoading(show = true) {
            const loadingDiv = document.getElementById('loading');
            if (loadingDiv) {
                loadingDiv.style.display = show ? 'flex' : 'none';
            }
        }
        
        // ตรวจสอบขนาดไฟล์ก่อนอัปโหลด
        function validateFileSize(input, maxSizeMB = 20) {
            if (input.files && input.files[0]) {
                const fileSizeMB = input.files[0].size / 1024 / 1024;
                if (fileSizeMB > maxSizeMB) {
                    showAlert(`ขนาดไฟล์เกิน ${maxSizeMB} MB`, 'error');
                    input.value = '';
                    return false;
                }
            }
            return true;
        }
        
        // แสดงชื่อไฟล์ที่เลือก
        function showFileName(input, displayElementId) {
            const displayElement = document.getElementById(displayElementId);
            if (input.files && input.files[0] && displayElement) {
                displayElement.textContent = input.files[0].name;
            }
        }
    </script>
    
    <!-- Loading Overlay -->
    <div id="loading" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 flex flex-col items-center space-y-4">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
            <p class="text-gray-700">กำลังประมวลผล...</p>
        </div>
    </div>
    
    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>
    
</body>
</html>