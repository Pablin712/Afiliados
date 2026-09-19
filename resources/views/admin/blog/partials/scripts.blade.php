{{--
    Shared behaviour for the blog editor: the Alpine component behind both
    modals plus the openers the table rows call. Included by the content admin
    page and by the writers' own page, so both drive the same modals.
--}}
@once
    @push('scripts')
        <script>
            (function () {
        const blogDeletePattern = @json(route('admin.blog.destroy', ['blogPost' => '__ID__']));

        function blogPostsData() {
            const node = document.getElementById('blog-posts-data');
            if (!node) return {};
            try {
                return JSON.parse(node.textContent) || {};
            } catch (e) {
                return {};
            }
        }

        function blankBlogPost() {
            return {
                title_es: '', title_en: '',
                excerpt_es: '', excerpt_en: '',
                body_es: '', body_en: '',
                slug: '', published_at: '',
                sort_order: 0, is_published: true,
            };
        }

        // Alpine component behind both blog modals: the create one
        // starts blank, the edit one is filled by load() with the
        // row's payload when the modal opens.
        window.blogPostForm = function (updatePattern) {
            return {
                updatePattern: updatePattern || null,
                post: blankBlogPost(),
                currentCoverUrl: null,
                publicUrl: null,
                load(payload) {
                    if (!payload) return;

                    this.post = Object.assign(blankBlogPost(), {
                        title_es: payload.title_es ?? '',
                        title_en: payload.title_en ?? '',
                        excerpt_es: payload.excerpt_es ?? '',
                        excerpt_en: payload.excerpt_en ?? '',
                        body_es: payload.body_es ?? '',
                        body_en: payload.body_en ?? '',
                        slug: payload.slug ?? '',
                        published_at: payload.published_at ?? '',
                        sort_order: payload.sort_order ?? 0,
                        is_published: !!payload.is_published,
                    });

                    this.currentCoverUrl = payload.coverUrl ?? null;
                    this.publicUrl = payload.publicUrl ?? null;

                    if (this.updatePattern) {
                        this.$el.action = this.updatePattern.replace('__ID__', String(payload.id));
                    }

                    const file = this.$el.querySelector('input[type="file"]');
                    if (file) file.value = '';
                },
            };
        };

        window.openBlogPostEditModal = function (id) {
            const payload = blogPostsData()[String(id)];
            if (!payload) return;

            window.dispatchEvent(new CustomEvent('blog-post-edit', { detail: payload }));
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'blog-post-edit-modal' }));
        };

        window.openBlogPostDeleteModal = function (id) {
            const payload = blogPostsData()[String(id)];
            const form = document.getElementById('blog-post-delete-form');
            if (!form || !payload) return;

            form.action = blogDeletePattern.replace('__ID__', String(payload.id));
            document.getElementById('blog-post-delete-name').textContent = payload.title ? `(${payload.title})` : '';
            window.dispatchEvent(new CustomEvent('open-modal', { detail: 'blog-post-delete-modal' }));
        };
            })();
        </script>
    @endpush
@endonce
