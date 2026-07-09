    </main>
    
    <footer style="margin-top: 4rem; padding: 4rem 0 2rem; background: linear-gradient(180deg, var(--bg-secondary) 0%, #EFF6FF 100%); border-top: 1px solid var(--glass-border);">
        <div class="container">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 3rem; margin-bottom: 3rem;">
                
                <div>
                    <a href="<?php echo getBaseUrl(); ?>/index.php" class="nav-brand" style="margin-bottom: 1.5rem; display: inline-flex;">
                        <i class="fas fa-graduation-cap"></i>
                        <span>أكاديمية ماستر</span>
                    </a>
                    <p style="color: var(--text-muted); font-size: 0.95rem; line-height: 1.8;">
                        منصة تعليمية متكاملة للطلاب المصريين في الثانوية العامة ونظام البكالوريا. نقدم تجربة تعليمية عصرية تجمع بين الدروس المسجلة والحصص المباشرة.
                    </p>
                    <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                        <a href="#" class="action-btn" style="background: rgba(37,99,235,0.1); color: var(--primary);"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="action-btn" style="background: rgba(37,99,235,0.1); color: var(--primary);"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="action-btn" style="background: rgba(37,99,235,0.1); color: var(--primary);"><i class="fab fa-youtube"></i></a>
                        <a href="#" class="action-btn" style="background: rgba(37,99,235,0.1); color: var(--primary);"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 1.25rem; font-size: 1.1rem; color: var(--primary);">روابط سريعة</h4>
                    <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 0.65rem;">
                        <li><a href="<?php echo getBaseUrl(); ?>/index.php" style="text-decoration: none; color: var(--text-muted);">الرئيسية</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>/index.php#courses" style="text-decoration: none; color: var(--text-muted);">الكورسات</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>/pages/teachers.php" style="text-decoration: none; color: var(--text-muted);">المعلمون</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>/pages/schedule.php" style="text-decoration: none; color: var(--text-muted);">الجدول الدراسي</a></li>
                        <li><a href="<?php echo getBaseUrl(); ?>/pages/about.php" style="text-decoration: none; color: var(--text-muted);">من نحن</a></li>
                    </ul>
                </div>
                
                <div>
                    <h4 style="margin-bottom: 1.25rem; font-size: 1.1rem; color: var(--primary);">تواصل معنا</h4>
                    <ul style="list-style: none; padding: 0; display: flex; flex-direction: column; gap: 0.75rem; color: var(--text-muted);">
                        <li style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-envelope" style="color: var(--primary);"></i>
                            info@masteracademy.eg
                        </li>
                        <li style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-phone" style="color: var(--primary);"></i>
                            +20 100 123 4567
                        </li>
                        <li style="display: flex; align-items: center; gap: 10px;">
                            <i class="fas fa-map-marker-alt" style="color: var(--primary);"></i>
                            القاهرة، مصر
                        </li>
                    </ul>
                </div>
                
            </div>
            
            <div style="padding-top: 2rem; border-top: 1px solid var(--glass-border); text-align: center; color: var(--text-muted); font-size: 0.9rem;">
                <p>&copy; <?php echo date('Y'); ?> أكاديمية ماستر (Master Academy). جميع الحقوق محفوظة.</p>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?php echo getBaseUrl(); ?>/js/main.js"></script>
</body>
</html>
