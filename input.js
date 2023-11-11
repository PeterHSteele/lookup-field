jQuery(document).ready(function(){
  (function(){
    class psDomNode {
      node = {};
  
      constructor( props ){
        for ( let prop in props ){
          this[prop] = props[prop];
        }
      }
  
      renderNode( node ){
        const el = document.createElement( node.tagName );
        if ( node.children ){
          const childNodes = [];
          node.children.forEach( child => childNodes.push( this.renderNode( child )));
          childNodes.forEach( child => el.append( child ));
        }
        if ( node.id ){
          el.setAttribute( 'id', node.id );
        }
        if ( node.classes ){
          node.classes.forEach( className => el.classList.add( className ));
        }
        if ( node.text ){
          const text = document.createTextNode( node.text() );
          el.append(text);
        } 
        if ( node.attrs ){
          node.attrs.forEach( attr => {
            el.setAttribute( attr[0], attr[1] );
          });
        }
        if ( node.aria ){
          Object.keys( node.aria ).forEach( key => el.setAttribute(`aria-${key}`, node.aria[key] ))
        }
        return el;
      }

      render(){
        return this.renderNode( this.node );
      }
    }

    class psLi extends psDomNode {
      node = {
        tagName: 'li',
        attrs: [ ['role', 'option'] ],
        classes: ['lf-listitem'],
        children: [
          {
            tagName: 'div',
            text: () => this.name,
          },{
            tagName: 'div',
            text: () => this.address,
            classes: ['lf-address']
          },
        ]
      }
    }

    const field = document.querySelector('.gfield--type-lookup');
    if (!field) return;
    const input=field.querySelector('input'),
    tooltip = field.querySelector('.lf-tooltip'),
    docList = tooltip.querySelector('ul');
    let apiUrl = input.dataset.url,
    focusedItem=null;

    if ( '/' == apiUrl.charAt(apiUrl.length-1)){
      apiUrl = apiUrl.slice(0,apiUrl.length-1);
    }

    let lookupFieldController = new AbortController();
  
    async function getData( term, abortSignal ){
      const q = 'provider_type:( "physician/internal medicine" OR "family practice" OR physician OR "Physician/Pediatric Medicine OR rural health clinic")';
      const url = `${apiUrl}?terms=${encodeURIComponent(term)}&maxList=8&q=${encodeURIComponent(q)}&df=name.full,addr_practice.full&sf=name.full`;//const url = apiUrl + '?terms=' + term + '&maxList=10',
      response = await fetch( url, {signal: abortSignal} ),
      json = await response.json();
      return json;
    }

    function makeLi( record, parent ){
      const li = new psLi({name: record[0], address: record[1]});
      return parent.append(li.render());
    }
    
    async function handleInput( event ){
      lookupFieldController.abort();
      lookupFieldController = new AbortController();
      while ( docList.lastElementChild ){
        docList.lastElementChild.remove();
      }
      const { value } = event.target;
      if ( value ){
        openTooltip();
        try {
          const results = await getData(value,lookupFieldController.signal);
          let doctors = results[3];
          //console.log(doctors)
          doctors.forEach( doctor => makeLi( doctor, docList ));
        } catch({message}){
          console.log(message);
        }
      } else {
        closeTooltip();
      }
    }

    function openTooltip(){
      tooltip.classList.add('is-visible');
      //input.setAttribute('aria-expanded', 'true');
    }

    function closeTooltip(){
      tooltip.classList.remove('is-visible');
      //input.setAttribute( 'aria-expanded', 'false' )
      focusedItem = null;
    }

    function handleListMouseDown( event ){
      const item = event.target.closest('.lf-listitem');
      if (item){
        const name = item.firstElementChild.textContent;
        input.value = name;
      }
    }

    function handleListClick( event ){
      input.scrollIntoView();
      closeTooltip()
    }

    function handleInputBlur(){
      if (tooltip.classList.contains('is-visible')){
        input.focus();
        input.scrollIntoView();
      }
      closeTooltip();
    }

    function shiftFocus( whichSibling ){
      if ( focusedItem && focusedItem[whichSibling] ){
        focusedItem[whichSibling].classList.add('lf-has-focus');
        focusedItem.classList.remove('lf-has-focus');
        focusedItem = focusedItem[whichSibling]
      } else if (whichSibling == "nextElementSibling" ){
        focusedItem = docList.firstElementChild;
        focusedItem.classList.add('lf-has-focus');
      }
    }

    function handleKeyDown( event ){
      if (tooltip.classList.contains('is-visible') && event.target.value ){
        switch (event.key){
          case 'Enter':
            event.preventDefault();
            const e = new MouseEvent('mousedown',{bubbles: true})
            const c = new MouseEvent('click',{bubbles:true});
            if (focusedItem){ 
              const ref = focusedItem
              ref.dispatchEvent(e);
              ref.dispatchEvent(c);
            }
            break;
          case 'ArrowDown':
            shiftFocus('nextElementSibling');
            break;
          case 'ArrowUp':
            shiftFocus('previousElementSibling');
            break;
          default:
        }
      }
    }

    input.addEventListener('input',handleInput);
    docList.addEventListener('mousedown',handleListMouseDown);
    docList.addEventListener('click',handleListClick);
    input.addEventListener('blur',handleInputBlur);
    input.addEventListener('keydown',handleKeyDown);

    })();
});