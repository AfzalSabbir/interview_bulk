<template>
    <div>
        <form method="get" @submit.prevent="getPostData()" accept-charset="utf-8" style="margin-bottom: 8px;">
            <div class="form-row">
                <div class="col-md-4 ">
                    <input class="form-control" type="text" v-model="search_text">
                </div>
                
                <div class="col-md-4 ">
                    <input class="form-control" type="date" v-model="search_time">
                </div>
                
                <div class="col-md-3 ">
                    <select class="form-control" v-model="search_group">
                        <option value="">All Group</option>
                        option
                        <option v-for="group in search_groups" :value="group.type">{{ group.type }}</option>
                    </select>
                </div>
                <div class="col-md-1 ">
                    <input type="submit" value="Search" class="btn btn-success">
                </div>
            </div>
        </form>
        <table class="table table-bordered table-striped table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Group Name</th>
                    <th>Group Type</th>
                    <th>Account Name</th>
                    <th>Post Text</th>
                    <th>Time</th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="(post, index) in posts.data">
                    <td>{{ index + this_page_min }}</td>
                    <td>{{ post.group_name }}</td>
                    <td>{{ post.group_type }}</td>
                    <td width="100"><img :src="post.account_photo" class="img-thumbnail" style="border-radius: 50%; width: 60px;" :alt="post.account_name"></td>
                    <td width="20%">{{ post.post_text }}</td>
                    <td>{{ post.created_at }}</td>
                </tr>
            </tbody>
        </table>
        <!-- .pagination -->
        <nav v-if="loaded && posts.data.length > 0 && pagination.last_page != 1" aria-label="...">
            <ul class="pagination pagination-sm justify-content-center">
                <li class="page-item cursor-pointer" :class="{ disabled: pagination.current_page <= 1 }">
                    <a class="page-link" @click.prevent="changePage(pagination.current_page - 1); getPostData(pagination.current_page)">Previous</a>
                </li>
                <li class="page-item cursor-pointer" :class="isCurrentPage(1) ? 'active' : ''">
                    <a class="page-link" @click.prevent="changePage(1); getPostData(1)"  >{{ '1' }}
                        <span v-if="isCurrentPage(1)" class="sr-only">(current)</span>
                    </a>
                </li>


                <li v-if="leftDots_" class="page-item cursor-default disabled">
                    <a class="page-link" @click.prevent="">...</a>
                </li>
                <li class="page-item cursor-pointer" v-for="page in pages" :key="page" :class="isCurrentPage(page) ? 'active' : ''">
                    <a class="page-link" @click.prevent="changePage(page); getPostData(page)">{{ page }}
                        <span v-if="isCurrentPage(page)" class="sr-only">(current)</span>
                    </a>
                </li>
                <li v-if="rightDots_" class="page-item cursor-default disabled">
                    <a class="page-link" @click.prevent="">...</a>
                </li>


                <li class="page-item cursor-pointer" :class="isCurrentPage(pagination.last_page) ? 'active' : ''">
                    <a class="page-link" @click.prevent="changePage(pagination.last_page); getPostData(pagination.last_page)">{{ pagination.last_page }}
                        <span v-if="isCurrentPage(pagination.last_page)" class="sr-only">(current)</span>
                    </a>
                </li>
                <li class="page-item cursor-pointer" :class="{ disabled: pagination.current_page >= pagination.last_page }">
                    <a class="page-link" @click.prevent="changePage(pagination.current_page + 1); getPostData(pagination.current_page)">Next</a>
                </li>
            </ul>
        </nav><!-- /.pagination -->
    </div>
</template>
<script>
export default {
    name: 'Post',
    props: [],
    data(){
        return {
            baseUrl        : 'http://localhost:8000',
            posts          : [],

            pagination     : [],
            offset         : 3,
            leftDots_      : false,
            rightDots_     : false,
            this_page_min  : 1,
            per_page_items : 20,
            this_page      : 1,

            loaded         : false,
            loading        : false,
            pageLoading    : true,
            noUpazilas     : true,
            noUnions       : true,

            search_text    : '',
            search_time    : '',
            search_group   : '',
            search_groups  : [],
        }
    },
    mounted(){
        this.getGroups();
        this.getPostData();
    },
    methods: {
        getPostData: function (page=0) {
            this.loading                 = true;
            
            let url = 'post?';

            url += 'search_text='+this.search_text;
            url += '&search_time='+this.search_time;
            url += '&search_group='+this.search_group;

            if (page == 0) {
                url += '&page='+1;
            } else {
                url += '&page='+page;
            }
            history.pushState('', 'title', url)

            axios.get(this.baseUrl+'/post/getPostData', {
                params         : {
                    search_text    : this.search_text,
                    search_time    : this.search_time,
                    search_group   : this.search_group,
                    page           : page,
                },
            }).then((response) => {
                // this.posts = response.data;
                // console.log(response.data);
                
                
                if (response.data.data.length > 0) {
                    this.posts         = response.data;
                    this.pagination    = response.data;
                    this.this_page_min = this.per_page_items * (this.this_page - 1) + 1;
                    this.changePage(this.pagination.current_page);
                } else {
                    this.posts          = [];
                    this.posts.data     = [];
                    this.this_page_min   = 1;
                }
                this.loaded = true;
            }).catch((error) => {
                console.error(error);
                this.loaded = true;
            });
        },
        getGroups: function () {
            axios.get(this.baseUrl+'/post/getGroups', {})
            .then((response) => {
                this.search_groups = response.data;
            }).catch((error) => {
                console.error(error);
            });
        },
        

        isCurrentPage(page){
            return this.pagination.current_page === page
        },
        changePage(page) {

            if (page > this.pagination.last_page) {
                page = this.pagination.last_page;
            }
            this.pagination.current_page = page;
            this.this_page               = page;

            if (this.offset >= page) this.leftDots_ = false;
            else this.leftDots_ = true;


            if (this.pagination.last_page - this.offset + 1 <= page) this.rightDots_ = false;
            else this.rightDots_ = true;


            if (this.pagination.last_page - 1 == this.offset) this.rightDots_ = false;

            // this.$emit('paginate');
        }
    },
    computed: {
      pages() {
        let pages = []
        let from = this.pagination.current_page - Math.floor(this.offset / 2)
        if (from < 1) {
          from = 1
        }
        let to = from + this.offset - 1

        if (to > this.pagination.last_page) {
          to = this.pagination.last_page
        }
        while (from <= to) {
          if (from > 1 && from < this.pagination.last_page) {
            pages.push(from)
          }
          from++
        }

        return pages
      }
    }
};
</script>