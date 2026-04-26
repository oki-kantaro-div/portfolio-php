            </div><!-- メインコンテンツ終了 -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // 削除確認ダイアログ
        function confirmDelete(id, title) {
            if (confirm('「' + title + '」を削除します。よろしいですか？')) {
                document.getElementById('delete-form-' + id).submit();
            }
        }
    </script>
</body>
</html>
