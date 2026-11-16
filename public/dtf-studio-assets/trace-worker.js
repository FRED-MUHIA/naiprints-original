'use strict';

function luminance(r,g,b){return .2126*r+.7152*g+.0722*b}
function hex(r,g,b){return '#'+[r,g,b].map(v=>Math.max(0,Math.min(255,Math.round(v))).toString(16).padStart(2,'0')).join('')}

function makePalette(data,count){
 const bins=new Map();
 for(let i=0;i<data.length;i+=4){if(data[i+3]<12)continue;const r=data[i],g=data[i+1],b=data[i+2],key=((r>>3)<<10)|((g>>3)<<5)|(b>>3);let v=bins.get(key);if(!v){v={r:0,g:0,b:0,n:0};bins.set(key,v)}v.r+=r;v.g+=g;v.b+=b;v.n++}
 let boxes=[Array.from(bins.values())];
 while(boxes.length<count){let best=-1,score=-1,channel=0;for(let i=0;i<boxes.length;i++){const box=boxes[i];if(box.length<2)continue;const mins=[255,255,255],maxs=[0,0,0];for(const p of box){const c=[p.r/p.n,p.g/p.n,p.b/p.n];for(let k=0;k<3;k++){mins[k]=Math.min(mins[k],c[k]);maxs[k]=Math.max(maxs[k],c[k])}}const ranges=maxs.map((v,k)=>v-mins[k]),ch=ranges.indexOf(Math.max(...ranges)),value=ranges[ch]*box.reduce((n,p)=>n+p.n,0);if(value>score){score=value;best=i;channel=ch}}
  if(best<0)break;const box=boxes.splice(best,1)[0].sort((a,b)=>a[['r','g','b'][channel]]/a.n-b[['r','g','b'][channel]]/b.n);const total=box.reduce((n,p)=>n+p.n,0);let acc=0,split=1;for(;split<box.length;split++){acc+=box[split-1].n;if(acc>=total/2)break}boxes.push(box.slice(0,split),box.slice(split))
 }
 return boxes.map(box=>{let r=0,g=0,b=0,n=0;for(const p of box){r+=p.r;g+=p.g;b+=p.b;n+=p.n}return{r:r/n,g:g/n,b:b/n,color:hex(r/n,g/n,b/n)}})
}

function trace({pixels,width,height,mode,threshold}){
 const data=new Uint8ClampedArray(pixels);if(width*height>1600000)throw Error('Image is too large to trace. Reduce the image size and try again.');
 const bw=mode==='bw',palette=bw?[{r:0,g:0,b:0,color:'#000000'},{r:255,g:255,b:255,color:'#ffffff'}]:makePalette(data,Math.max(2,Math.min(16,Number(mode)||3)));
 if(!palette.length)throw Error('No visible pixels were found to trace.');
 const ids=new Uint8Array(width*height),alphas=new Uint8Array(width*height);for(let p=0,i=0;p<ids.length;p++,i+=4){const a=data[i+3];if(a<12){ids[p]=255;continue}alphas[p]=Math.min(255,Math.round(a/17)*17);if(bw){ids[p]=luminance(data[i],data[i+1],data[i+2])<threshold?0:1;continue}let best=Infinity,index=0;for(let j=0;j<palette.length;j++){const dr=data[i]-palette[j].r,dg=data[i+1]-palette[j].g,db=data[i+2]-palette[j].b,d=dr*dr*.30+dg*dg*.59+db*db*.11;if(d<best){best=d;index=j}}ids[p]=index}
 const paths=[];let active=new Map();for(let y=0;y<height;y++){const next=new Map();let x=0;while(x<width){const p=y*width+x,id=ids[p];if(id===255){x++;continue}const alpha=alphas[p],key=id+':'+alpha,x0=x;x++;while(x<width&&ids[y*width+x]===id&&alphas[y*width+x]===alpha)x++;const x1=x,runKey=key+':'+x0+':'+x1,prior=active.get(runKey);if(prior){prior.h++;next.set(runKey,prior)}else next.set(runKey,{id,alpha,x:x0,y,h:1,w:x1-x0})}for(const [key,path] of active)if(!next.has(key))paths.push(path);active=next}for(const path of active.values())paths.push(path);
 const groups=new Map();for(const p of paths){const key=p.id+':'+p.alpha;let group=groups.get(key);if(!group){group={id:p.id,alpha:p.alpha,d:''};groups.set(key,group)}group.d+=`M${p.x} ${p.y}h${p.w}v${p.h}h-${p.w}z`}
 const body=[...groups.values()].map(g=>`<path fill="${palette[g.id].color}"${g.alpha<255?` fill-opacity="${(g.alpha/255).toFixed(3)}"`:''} d="${g.d}"/>`).join('');const svg=`<svg xmlns="http://www.w3.org/2000/svg" width="${width}" height="${height}" viewBox="0 0 ${width} ${height}" shape-rendering="crispEdges">${body}</svg>`;return{svg,paths:paths.length}
}

self.onmessage=e=>{try{self.postMessage(trace(e.data))}catch(error){self.postMessage({error:error.message||'Image trace failed.'})}};
