<template>
    <div>
        <section class="todoapp">
            <header class="header">
                <h1>Todo App</h1>
                <input class="new-todo" v-model="newTodo" @keyup.enter="addTodo" autofocus autocomplete="off" placeholder="What needs to be done?" />
            </header>
            <section class="main" >
                <input  id="toggle-all"  class="toggle-all" type="checkbox"  :checked="!anyRemaining" @change="checkAllTodos()"/>
                <label for="toggle-all"></label>
                <ul class="todo-list">
                    <li class="todo" v-for="(todo, index) in todosFiltered" :key="todo.id" :class="{ completed: todo.completed, editing: todo.editing }">
                        <div class="view" v-if="!todo.editing">
                            <input class="toggle" type="checkbox"  v-model="todo.completed" @change="doneEditing(todo)"/>
                            <label @dblclick="editTodo(todo)">{{todo.title}}</label>
                            <button class="destroy" @click="removeTodoItem(todo.id)"></button>
                        </div>
                        <input v-else class="edit" type="text" v-model="todo.title" @blur="doneEditing(todo)" @keyup.esc="cancelEditing(todo)" @keyup.enter="doneEditing(todo)" v-focus/>
                    </li>
                </ul>
            </section>
            <footer class="footer" v-show="todos.length > 0">
        <span class="todo-count">
          <strong>{{ remaining }} items left</strong>
        </span>
                <ul class="filters">
                    <li>
                        <button class="button-filters" :class="{ active: filter == 'all' }" @click="filter = 'all'">All</button>
                    </li>
                    <li>
                        <button class="button-filters" :class="{ active: filter == 'active' }" @click="filter = 'active'">Active</button>
                    </li>
                    <li>
                        <button class="button-filters" :class="{ active: filter == 'completed' }" @click="filter = 'completed'">Completed</button>
                    </li>
                </ul>
                <button v-if="showClearCompletedButton" class="clear-completed" @click="clearCompleted">Clear completed</button>
            </footer>
        </section>
    </div>
</template>

<script>
    export default {
        name: 'TodoList',
        data(){
            return{
                newTodo: '',
                todos: [],
                filter: 'all',
                editBeforeCache: ''
            }
        },
        created(){
            this.getAllTodos();
        },
        computed:{
            remaining(){
                return this.todos.filter(todo => !todo.completed).length;
            },
            anyRemaining(){
                return this.remaining != 0;
            },
            todosFiltered(){
                if(this.filter == 'all'){
                    return this.todos;
                }else if(this.filter == 'active'){
                    return this.todos.filter(todo => !todo.completed)
                }else if(this.filter == 'completed'){
                    return this.todos.filter(todo => todo.completed)
                }
                return this.todos;
            },
            showClearCompletedButton(){
                return this.todos.filter(todo => todo.completed).length > 0
            }
        },
        directives: {
            focus: {
                inserted: function (el) {
                    el.focus()
                }
            }
        },
        methods:{
            getAllTodos(){
                axios.get('/api/todos')
                    .then(({data})  => (
                        this.todos = data.map(todo => (Object.assign({}, todo, {editing: false})))
                    ))
                    .catch (error => {
                        console.log(error)
                    })
            },
            addTodo(){
                //check input value is empty or not
                if(this.newTodo.trim().length == 0){
                    return;
                }
                //save data to database
                axios.post('/api/todos', {
                    title: this.newTodo,
                    completed: false
                })
                    .then(({data})  => (
                        this.todos.push({
                            'id': data.id,
                            'title': data.title,
                            'completed': data.completed,
                            'editing': false
                        })
                    ))
                    .catch (error => {
                        console.log(error)
                    });
                this.newTodo = '';
            },
            editTodo(todo){
                this.editBeforeCache= todo.title
                todo.editing = true;
            },
            doneEditing(todo){
                if(todo.title.trim().length == 0){
                    todo.title = this.editBeforeCache;
                }
                //update data to database
                axios.patch('/api/todos/' + todo.id, {
                    title: todo.title,
                    completed: todo.completed
                })
                    .then(({data})  => (
                        this.afterUpdate(data, todo)
                    ))
                    .catch (error => {
                        console.log(error)
                    });
                todo.editing = false;
            },
            cancelEditing(todo){
                todo.title = this.editBeforeCache
                todo.editing = false;
            },
            removeTodoItem(id){
                //delete data to database
                axios.delete('/api/todos/' + id)
                    .then(({data})  => (
                       this.afterDelete(id)
                    ))
                    .catch (error => {
                        console.log(error)
                    });
            },
            checkAllTodos(){
                //update data to database
                axios.patch('/api/todosAllChecked', {
                    completed: event.target.checked
                })
                    .then( this.todos.forEach(todo => todo.completed = event.target.checked))
                    .catch (error => {
                        console.log(error)
                    });
            },
            clearCompleted(){
                const completed = this.todos.filter(todo => todo.completed).map(todo => todo.id)
                //delete all completed data from database
                axios.delete('/api/todosDeleteCompleted', {
                    data: {
                        todos: completed
                    }
                })
                    .then(this.todos = this.todos.filter(todo => !todo.completed))
                    .catch (error => {
                        console.log(error)
                    });
            },
            afterUpdate(data, todo){
                const index = this.todos.findIndex(item => item.id == todo.id);
                this.todos.splice(index, 1, {
                    'id': data.id,
                    'title': data.title,
                    'completed': data.completed,
                    'editing': data.editing
                });
            },
            afterDelete(id){
                const index = this.todos.findIndex(item => item.id == id);
                this.todos.splice(index, 1)
            }
        }
    }
</script>

<style scoped>
    .button-filters {
        margin: 0 5px 0 0;
        padding: 5px;
        border: 1px solid grey;
        border-radius: 3px;
        background: white;
        font-size: 100%;
        font-weight: bold;
        color: grey;
        -webkit-appearance: none;
        appearance: none;
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        cursor: pointer;
    }

    .active {
        background-color: green;
        color: black;
    }


</style>

