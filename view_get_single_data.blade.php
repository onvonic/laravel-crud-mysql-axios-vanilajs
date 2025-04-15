<script>
    document.addEventListener('DOMContentLoaded', async function() {
        try {
            const id = edPostId;
            const url = `{{ route('app.posts.data') }}?id=${id}`;

            const response = await axios.get(url);
            const data = response.data.data;

            edPostTitle.value = data.post_title;
            edPostSlug.value = data.post_slug;
            ......
            PreviewInputImage.value = data.post_image;
            
        } catch (error) {
            console.error('Error fetching data:', error);
        }
    });
</script>
