{!! $customView !!}
@section('andersonef.ghBaseComponent.javascript')
    <script type="text/javascript">
        const Componente = {
            EVENTS : {
                ON_FINISH : 'ONFINISH',
                ON_BEFORE_CLICK : 'ONBEFORECLICK',
                ON_CLEAR : 'ONCLEAR'
            },
            EventStack : new HashMap(),

            EventObject : function(object, event, listener){
                this.object = object;
                this.event = event;
                this.listener = listener;

                this.triggerEvent = function(params){
                    this.listener(params);
                }
                this.getObject = function(){
                    return this.object;
                }
            },

            addEventListener : function(object, event, listener){
                var evt = new this.EventObject(object, event, listener);
                var eventos = this.EventStack.get(event) || [];
                eventos.push(evt);
                this.EventStack.put(event, eventos);
                return this;
            },

            triggerEvent : function(object, event, params){
                var eventos = this.EventStack.get(event); //lista de todos os eventos do tipo solicitado:
                for(var i in eventos){
                    if(!Object.is(object, eventos[i].getObject())) continue; //coisa linda de Deus esse Object.is
                    eventos[i].triggerEvent(params);
                }
            },


            /**
             * Hack para algumas telas. Isola um determinado bloco de código em um escopo próprio dos componentes
             * @param callback
             */
            scope : function(callback){
                setTimeout(callback, 0);
            },

            /**
             * Prepara um novo componente, adicionando métodos e properties padrões para todos
             * @param implementacao
             * @returns {*}
             */
            newComponente : function(implementacao){
                var self = this;
                implementacao._isDebug = false;
                implementacao.name;
                implementacao.$hidden = null;
                implementacao.Animal = null;
                implementacao.attributes;
                implementacao.params = {};
                implementacao.$searchButton = null;
                implementacao.selectCallback = null;
                implementacao.isUsingQuery = false;
                implementacao._isMultiple = false;
                implementacao.selectedItems = new HashMap();

                /** MÉTODOS **/

                /**
                 * Seta o componente como sendo múltiplo ou não.
                 * @param multiple boolean default true
                 * @returns this
                 */
                implementacao.setMultiple      = function(multiple){
                    this._isMultiple = multiple || true;
                    return this;
                }


                /**
                 * Retorna um plain-object contendo como properties os atributos do dom node <componente>
                 * @returns {*}
                 */
                implementacao.getAttributes     = function(){
                    return this.attributes;
                }

                /**
                 * Retorna um plain-object contendo como properties os atributos do dom node <componente> que iniciam com data-, ex: data-sexoAnimal="M", retorna: {sexoAnimal: "M"}
                 * @returns {*}
                */
                implementacao.getDataAttributes = function(){
                    var retorno = {};
                    for(var prop in this.attributes){
                        if(prop.substring(0,5) == 'data-') continue;
                        retorno[prop.substring(5)] = this.attributes[prop];
                    }
                    return retorno;
                }

                /**
                 *  Retorna boleano indicando se o componente está em modo debug ou não
                 * @returns boolean
                 */
                implementacao.isDebug           = function(){
                    return this._isDebug;
                }

                /**
                 * Seta o componente como modo debug ou não. Se não for informado nenhum parâmetro, o default é true
                 *  * Usa chaining
                 *
                 * @param bolean using default true
                 * @returns this
                 */
                implementacao.usingDebug        = function(using){
                    this._isDebug = using || true;
                    return this;
                }

                /**
                 * Adiciona um callback na pilha de eventos informada
                 *  * Usa chaining
                 * @param event constante que deve estar em Componente.EVENTS
                 * @param listener callback que será disparado
                 * @returns this
                 */
                implementacao.addEventListener = function(event, listener){
                    self.addEventListener(this, event, listener);
                    return this;
                };

                /**
                 * Dispara um determinado evento. Utilizado internamente
                 * @param event
                 * @param params
                 * @returns {boolean}
                 */
                implementacao.triggerEvent      = function(event, params){
                    try{
                        self.triggerEvent(this, event, params);
                        return true;
                    }catch(e){
                        if(this.isDebug()) alert(e);
                        return false;
                    }
                }


                /**
                 * Seta o input hidden do componente como property
                 * @param hidden
                 * @returns {implementacao}
                 */
                implementacao.setHidden         = function(hidden){
                    this.$hidden = hidden;
                    return this;
                }


                /**
                 * Marca o componente com a utilização de query. Isso faz com que a response no onfinish não seja um objeto ou vetor de objetos, mas sim um objeto query
                 * pra ser usado no backend
                 * @param use
                 * @returns {implementacao}
                 */
                implementacao.useQuery        = function(use){
                    this.isUsingQuery = use || true
                    return this;
                }

                /**
                 * Setter do botão de busca, que ativa o componente. Permite uso de chaining
                 * @param btn
                 * @returns {implementacao}
                 */
                implementacao.setSearchButton = function(btn){
                    var comp = this;
                    if(!btn.jquery) throw "Componente Animal: o objeto passado para o método setSearchButton deve ser um jQuery Object!";
                    this.$searchButton = btn;
                    btn.on('click', function(){
                        if(!comp.triggerEvent(Componente.EVENTS.ON_BEFORE_CLICK)) return;
                        comp.onSearchButtonClick();
                    });
                    return this;
                }

                /**
                 * Método principal do componente. Deve ser implementado na classe do componente.
                 * @type {*|Function}
                 */
                implementacao.onSearchButtonClick = implementacao.onSearchButtonClick || function(){
                    throw "onSearchButtonClick não implementado no componente solicitado";
                }
                return implementacao;
            },


            /**
             * Método responsável pela criação de novos Factories. Inicializa funções mais comuns nas factories de componentes
             * @param implementacao
             * @returns {*}
             */
            newFactory : function(implementacao){
                var self = this;
                implementacao._items = new HashMap();

                implementacao.get           = function(key){
                    return this._items.get(key);
                }

                implementacao._initialize = function(uniqueItem, type){
                    if(!uniqueItem || !uniqueItem.jquery) throw "Componente Animal: Se deseja inicializar um único objeto, ele deve ser um jquery object";

                    if(uniqueItem.data()._componente) return; //já foi inicializado


                    uniqueItem.prop('type', 'hidden');
                    var comp = Componente.newComponente(new type(uniqueItem.attr('name'), uniqueItem.getAttributes()));
                    uniqueItem.data()._componente = comp;
                    this._items.put(uniqueItem.attr('name'), comp);

                    //tem searchButton?
                    if(uniqueItem.attr('dispatcher-button')){
                        comp.setSearchButton($(uniqueItem.attr('dispatcher-button')));
                    }
                    //é multiple?
                    if(uniqueItem.attr('multiple')){
                        comp.setMultiple(true);
                    }
                }
                return implementacao;
            }
        };
    </script>
    @yield('ComponentJavascript')
    @parent
@endsection