<template>
    <div>
        <h1>Inertiajs is {{ test }}</h1>
        asfasfasd
        <Link href="/about-us">Go to about us</Link>
    </div>
</template>

<script>
import { Link } from "@inertiajs/inertia-vue3";
export default {
    components: {
        Link,
    },
    props: ["test"],
};
</script>
